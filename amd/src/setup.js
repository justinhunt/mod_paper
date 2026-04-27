define(['jquery', 'core/log'], function($, log) {
    "use strict";

    var newIdCounter = 1;

    function bindVanillaJSInteractions(boxElement) {
        var isDragging = false;
        var isResizing = false;
        var startX, startY, startLeft, startTop, startWidth, startHeight;
        var currentHandle = '';

        // Add resize handles
        var nw = $('<div class="resize-handle nw" style="position:absolute; width:10px; height:10px; background:white; border:1px solid black; top:-5px; left:-5px; cursor:nwse-resize;"></div>');
        var ne = $('<div class="resize-handle ne" style="position:absolute; width:10px; height:10px; background:white; border:1px solid black; top:-5px; right:-5px; cursor:nesw-resize;"></div>');
        var sw = $('<div class="resize-handle sw" style="position:absolute; width:10px; height:10px; background:white; border:1px solid black; bottom:-5px; left:-5px; cursor:nesw-resize;"></div>');
        var se = $('<div class="resize-handle se" style="position:absolute; width:10px; height:10px; background:white; border:1px solid black; bottom:-5px; right:-5px; cursor:nwse-resize;"></div>');
        boxElement.append(nw).append(ne).append(sw).append(se);

        boxElement.on('mousedown', function(e) {
            e.preventDefault();
            selectArea(boxElement.attr('data-area-id'));

            var container = $('#image-container');
            startX = e.clientX;
            startY = e.clientY;
            startLeft = parseFloat(boxElement.css('left')) || 0;
            startTop = parseFloat(boxElement.css('top')) || 0;
            startWidth = parseFloat(boxElement.css('width')) || 0;
            startHeight = parseFloat(boxElement.css('height')) || 0;

            if ($(e.target).hasClass('resize-handle')) {
                isResizing = true;
                if ($(e.target).hasClass('nw')) {
                    currentHandle = 'nw';
                }
                if ($(e.target).hasClass('ne')) {
                    currentHandle = 'ne';
                }
                if ($(e.target).hasClass('sw')) {
                    currentHandle = 'sw';
                }
                if ($(e.target).hasClass('se')) {
                    currentHandle = 'se';
                }
            } else {
                isDragging = true;
                boxElement.css('cursor', 'move');
            }

            $(document).on('mousemove.paperBox', function(me) {
                var containerW = container.width();
                var containerH = container.height();
                var dx = me.clientX - startX;
                var dy = me.clientY - startY;

                if (isDragging) {
                    var newLeft = startLeft + dx;
                    var newTop = startTop + dy;
                    boxElement.css({ left: newLeft + 'px', top: newTop + 'px' });
                } else if (isResizing) {
                    if (currentHandle === 'se') {
                        boxElement.css({ width: (startWidth + dx) + 'px', height: (startHeight + dy) + 'px' });
                    } else if (currentHandle === 'sw') {
                        boxElement.css({ left: (startLeft + dx) + 'px', width: (startWidth - dx) + 'px', height: (startHeight + dy) + 'px' });
                    } else if (currentHandle === 'ne') {
                        boxElement.css({ top: (startTop + dy) + 'px', width: (startWidth + dx) + 'px', height: (startHeight - dy) + 'px' });
                    } else if (currentHandle === 'nw') {
                        boxElement.css({ left: (startLeft + dx) + 'px', top: (startTop + dy) + 'px', width: (startWidth - dx) + 'px', height: (startHeight - dy) + 'px' });
                    }
                }

                // Update input form percentages
                var areaId = boxElement.attr('data-area-id');
                var cLeft = parseFloat(boxElement.css('left')) / containerW * 100;
                var cTop = parseFloat(boxElement.css('top')) / containerH * 100;
                var cWidth = parseFloat(boxElement.css('width')) / containerW * 100;
                var cHeight = parseFloat(boxElement.css('height')) / containerH * 100;

                $('input.pos-input[data-area="' + areaId + '"][data-attr="left"]').val(cLeft.toFixed(1));
                $('input.pos-input[data-area="' + areaId + '"][data-attr="top"]').val(cTop.toFixed(1));
                $('input.pos-input[data-area="' + areaId + '"][data-attr="width"]').val(cWidth.toFixed(1));
                $('input.pos-input[data-area="' + areaId + '"][data-attr="height"]').val(cHeight.toFixed(1));
            });

            $(document).on('mouseup.paperBox', function() {
                isDragging = false;
                isResizing = false;
                boxElement.css('cursor', 'default');
                $(document).off('mousemove.paperBox mouseup.paperBox');
            });
        });
    }

    function selectArea(areaId) {
        $('.paper-box').css('border', '2px solid green').css('z-index', 10);
        $('#box_' + areaId).css('border', '3px solid blue').css('z-index', 20);
        
        $('.area-card').hide();
        $('#card_' + areaId).fadeIn();
    }

    function createBoxElement(id, num, x, y, w, h) {
        var box = $('<div/>', {
            id: 'box_' + id,
            'class': 'paper-box',
            'data-area-id': id,
            css: {
                position: 'absolute',
                border: '2px solid green',
                backgroundColor: 'rgba(0,128,0,0.2)',
                left: x + '%',
                top: y + '%',
                width: w + '%',
                height: h + '%'
            },
            title: 'Response Area #' + num
        });
        
        var label = $('<span/>', {
            'class': 'box-label',
            text: '#' + num,
            css: {
                backgroundColor: 'green',
                color: 'white',
                padding: '2px 5px',
                position: 'absolute',
                top: 0,
                left: 0,
                fontSize: '12px'
            }
        });
        
        box.append(label);
        $('#image-container').append(box);
        bindVanillaJSInteractions(box);
        return box;
    }

    function renderStatusIcons() {
        var container = $('#status-icons-container');
        if (!container.length) {
            return;
        }
        
        container.empty();
        
        var num = 1;
        var totalAreas = 0;
        var configuredAreas = 0;
        
        $('#setup-form .area-card').each(function() {
            totalAreas++;
            var areaId = $(this).data('area-id');
            var questionVal = $(this).find('textarea[name^="question"]').val() || '';
            var nftVal = $(this).find('.namefield-radio:checked').val();
            var isNameField = (nftVal === '1' || nftVal === '2' || nftVal === '3');
            
            var isConfigured = (questionVal.trim() !== '') || isNameField;
            if (isConfigured) {
                configuredAreas++;
            }
            
            var iconClass = isConfigured ? 'fa-check-square-o text-success' : 'fa-square-o text-secondary';
            
            var iconWrapper = $('<div/>', {
                'class': 'status-icon mr-3',
                'data-area-id': areaId,
                'title': 'Area #' + num + (isConfigured ? ' (Configured)' : ' (Not configured)'),
                css: { cursor: 'pointer', display: 'inline-flex', alignItems: 'center', gap: '5px' }
            });
            
            iconWrapper.append($('<i/>', { 'class': 'fa ' + iconClass + ' fa-lg' }));
            iconWrapper.append($('<span/>', { text: '#' + num, css: { fontWeight: 'bold' } }));
            
            iconWrapper.on('click', function() {
                selectArea(areaId);
            });
            
            container.append(iconWrapper);
            num++;
        });

        var summaryLabel = $('#status-summary-label');
        if (summaryLabel.length) {
            if (totalAreas === 0) {
                summaryLabel.text('');
            } else if (configuredAreas === totalAreas) {
                summaryLabel.text('All configured!!').removeClass('text-secondary text-warning').addClass('text-success');
            } else if (configuredAreas === 0) {
                summaryLabel.text('None configured.').removeClass('text-success text-warning').addClass('text-secondary');
            } else {
                summaryLabel.text(configuredAreas + '/' + totalAreas + ' configured.').removeClass('text-success text-secondary').addClass('text-warning');
            }
        }
    }

    function updateLabels() {
        var num = 1;
        $('#setup-form .area-card').each(function() {
            var areaId = $(this).data('area-id');
            $(this).find('.area-display-num').text('#' + num);
            $('#box_' + areaId).find('.box-label').text('#' + num);
            num++;
        });
        renderStatusIcons();
    }

    return {
        init: function(params) {
            log.debug("mod_paper setup initialized", params);
            
            var container = $('#image-container');
            
            if (params.imagebase64) {
                var img = $('<img/>', {
                    src: 'data:image/jpeg;base64,' + params.imagebase64,
                    css: {
                        width: '100%',
                        height: 'auto',
                        display: 'block'
                    }
                });
                
                img.on('load', function() {
                    // Draw bounding boxes once image is loaded
                    if (params.responseareas && params.responseareas.length > 0) {
                        params.responseareas.forEach(function(area) {
                            createBoxElement(area.id, area.responsenumber, area.box_x, area.box_y, area.box_w, area.box_h);
                        });
                        // Select first by default
                        selectArea(params.responseareas[0].id);
                        renderStatusIcons();
                    }
                });
                
                container.empty().append(img);
            }

            // Bind live updates for manual coordinate input tweaking
            $('#setup-form').on('input', '.pos-input', function() {
                var val = $(this).val();
                var areaId = $(this).data('area');
                var attr = $(this).data('attr');
                if (val !== '') {
                    $('#box_' + areaId).css(attr, val + '%');
                }
            });

            // Toggle correct answer textarea based on radio selection
            $('#setup-form').on('change', '.cam-radio', function() {
                var areaId = $(this).data('area-id');
                var val = $(this).val();
                if (val === 'none' || val === 'relevant') {
                    $('#ca_textarea_' + areaId).prop('disabled', true);
                } else {
                    $('#ca_textarea_' + areaId).prop('disabled', false);
                }
            });
            
            // Trigger change initially for existing cards
            $('.cam-radio:checked').trigger('change');

            // Handle namefield radio changes
            $('#setup-form').on('change', '.namefield-radio', function() {
                var areaId = $(this).closest('.area-card').data('area-id');
                var val = $(this).val();
                
                // If it's 1 or 2, uncheck it in other cards
                if (val === '1' || val === '2') {
                    $('#setup-form .area-card').each(function() {
                        var otherId = $(this).data('area-id');
                        if (otherId !== areaId) {
                            var otherRadio = $(this).find('.namefield-radio[value="' + val + '"]');
                            if (otherRadio.prop('checked')) {
                                // Revert to 'Not a name field' (0)
                                $(this).find('.namefield-radio[value="0"]').prop('checked', true).trigger('change');
                            }
                        }
                    });
                }
                
                // Disable/enable other fields in THIS card based on whether it is a name field
                var isNameField = (val === '1' || val === '2' || val === '3');
                var card = $('#card_' + areaId);
                
                // Fields to disable: question, correctanswer, correctanswermode radios, grammarcorrections radios, maxgrade etc.
                card.find('fieldset').not('.fieldset-namefield').find('input, textarea').prop('disabled', isNameField);
                
                renderStatusIcons();
            });
            
            // Trigger namefield radio changes for initial state
            $('.namefield-radio:checked').trigger('change');

            // Ensure disabled fields are re-enabled upon submission so we don't lose data
            $('#setup-form').on('submit', function() {
                $(this).find(':disabled').prop('disabled', false);
            });

            // Live update status icons when question changes
            $('#setup-form').on('input change', 'textarea[name^="question"]', function() {
                renderStatusIcons();
            });

            // Add new area
            $('#btn-add-area').on('click', function(e) {
                e.preventDefault();
                var newId = 'new_' + newIdCounter++;
                var num = $('#setup-form .area-card').length + 1;
                
                // Clone HTML blueprint
                var blueprint = $('#area-card-blueprint').html();
                blueprint = blueprint.replace(/NEWNUM/g, num).replace(/NEWID/g, newId);
                var newCard = $(blueprint);
                
                // Keep classes clean without blueprint bindings
                newCard.find('.blueprint-input').removeClass('blueprint-input');
                
                // Append
                $('#setup-form > button[type="submit"]').before(newCard);
                
                // Create box at 10% 10%
                createBoxElement(newId, num, 10, 10, 20, 5);
                updateLabels();
                selectArea(newId);
            });

            // Trash area
            $('#setup-form').on('click', '.btn-trash-area', function(e) {
                e.preventDefault();
                if (confirm("Remove this response area?")) {
                    var areaId = $(this).data('area-id');
                    $('#card_' + areaId).remove();
                    $('#box_' + areaId).remove();
                    updateLabels();
                    
                    // Select first available or hide
                    var firstCard = $('#setup-form .area-card').first();
                    if (firstCard.length > 0) {
                        selectArea(firstCard.data('area-id'));
                    }
                }
            });

            // Handle preset selection
            var presetContents = params.preset_contents_json || {};
            if (typeof presetContents === 'string') {
                try {
                    presetContents = JSON.parse(presetContents);
                } catch (e) {
                    log.error("Failed to parse preset contents", e);
                    presetContents = {};
                }
            }
            $('#setup-form').on('change', '.preset-select', function() {
                var areaId = $(this).data('area-id');
                var val = $(this).val();
                if (val !== '0' && presetContents[val]) {
                    $('#gi_' + areaId).val(presetContents[val]);
                    // Trigger input to update status icons if needed
                    $('#gi_' + areaId).trigger('input');
                }
            });
        }
    };
});

define(['jquery', 'core/ajax', 'core/notification', 'core/str'], function($, Ajax, Notification, Str) {
    return {
        init: function(cmid, evalid, maxpossible) {
            console.log('mod_paper view_eval init:', cmid, evalid, maxpossible);

            $('.eval-item-area').on('click', function() {
                var area = $(this);
                var itemId = area.data('item-id');
                var areaId = area.data('area-id');
                var isNameField = area.data('is-name-field');
                var ocr = area.data('ocr');
                var corrected = area.data('corrected');
                var feedback = area.data('feedback');
                var grade = area.data('grade');
                var areaNum = area.data('responsenumber');

                // Fill form
                $('#field-itemid').val(itemId);
                $('#field-areaid').val(areaId);
                $('#field-grade').val(grade);
                $('#field-ocr-readonly').text(ocr);
                $('#field-correctedtext').val(corrected);
                $('#field-feedback').val(feedback);
                $('#sidebar-area-num').text(areaNum ? '#' + areaNum : '');

                // Toggle fields for name field
                if (isNameField) {
                    $('#group-grade').hide();
                    $('#group-feedback').hide();
                } else {
                    $('#group-grade').show();
                    $('#group-feedback').show();
                }

                $('#edit-sidebar').show();
                
                // Highlight active area
                $('.eval-item-area').css('outline', '2px solid blue');
                area.css('outline', '4px solid red');
            });

            $('#btn-cancel-edit').on('click', function() {
                $('#edit-sidebar').hide();
                $('.eval-item-area').css('outline', '2px solid blue');
            });

            $('#edit-item-form').on('submit', function(e) {
                e.preventDefault();
                
                var formData = {
                    itemid: $('#field-itemid').val(),
                    areaid: $('#field-areaid').val(),
                    grade: $('#field-grade').val(),
                    correctedtext: $('#field-correctedtext').val(),
                    feedback: $('#field-feedback').val(),
                    evalid: evalid,
                    cmid: cmid,
                    sesskey: M.cfg.sesskey
                };

                $('#btn-save-item').prop('disabled', true).text('Saving...');

                var url = M.cfg.wwwroot + '/mod/paper/ajax_update_eval.php?' + $.param(formData);

                fetch(url)
                    .then(response => response.json())
                    .then(result => {
                        $('#btn-save-item').prop('disabled', false).text('Save Changes');
                        
                        if (result.success) {
                            // Update the area HTML
                            var areaId = '#item_area_' + formData.areaid;
                            $(areaId).html(result.newhtml);
                            
                            // Update data attributes
                            $(areaId).data('item-id', result.itemid);
                            $(areaId).data('corrected', formData.correctedtext);
                            $(areaId).data('feedback', formData.feedback);
                            $(areaId).data('grade', formData.grade);
                            $(areaId).attr('data-item-id', result.itemid); // Ensure attribute is updated too
                            
                            // Update badge
                            var badgeId = '#item_grade_badge_' + formData.areaid;
                            if (formData.grade !== '' && !$(areaId).data('is-name-field')) {
                                $(badgeId).text(formData.grade).show();
                            } else {
                                $(badgeId).hide();
                            }

                            // Update total grade
                            $('#total-grade-display').text(result.totalgrade + ' / ' + maxpossible);
                            
                            Notification.addNotification({
                                message: 'Changes saved successfully',
                                type: 'success'
                            });
                            
                            $('#edit-sidebar').hide();
                            $('.eval-item-area').css('outline', '2px solid blue');
                        } else {
                            Notification.alert('Error', result.error || 'Failed to save changes', 'OK');
                        }
                    })
                    .catch(error => {
                        $('#btn-save-item').prop('disabled', false).text('Save Changes');
                        Notification.exception(error);
                    });
            });
        }
    };
});

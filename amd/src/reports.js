define(['core/ajax', 'core/log'], function(Ajax, log) {
    return {
        init: function(cmid, currentCount, isPending) {
            var interval = setInterval(function() {
                Ajax.call([{
                    methodname: 'mod_paper_check_status',
                    args: {
                        id: cmid,
                        currentcount: currentCount
                    }
                }])[0].then(function(data) {
                    // Reload if new evaluations have appeared (count changed)...
                    if (data.count !== currentCount) {
                        clearInterval(interval);
                        window.location.reload();
                        return;
                    }
                    // ...or if previously-pending items are now complete.
                    if (isPending && data.complete) {
                        clearInterval(interval);
                        window.location.reload();
                    }
                }).catch(function(ex) {
                    log.error('Error checking status:', ex);
                });
            }, 5000);
        }
    };
});

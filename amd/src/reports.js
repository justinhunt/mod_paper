define(['core/ajax', 'core/log'], function(Ajax, log) {
    return {
        init: function(cmid, currentCount, isPending) {
            // Only poll if we are actually waiting for something.
            if (!isPending && currentCount > 0) {
                return;
            }

            var interval = setInterval(function() {
                Ajax.call([{
                    methodname: 'mod_paper_check_status',
                    args: {
                        id: cmid,
                        currentcount: currentCount
                    }
                }])[0].then(function(data) {
                    // Refresh if everything is now complete AND (it wasn't before OR count changed).
                    if (data.complete && (isPending || data.count !== currentCount)) {
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

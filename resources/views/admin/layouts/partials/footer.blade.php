<footer class="main-footer">
    <script>
        var sessionTimeout = {{ config('session.lifetime') * 60 }}; // Convert minutes to seconds

        function isSessionTimeout() {
            //$sessionTimeout = sessionTimeout; // Session timeout in seconds

            var sess = {{ session()->has('lastActivity') }};
            // Check if lastActivity session variable exists
            if (sess) {
                var lastActivity = {{ session()->get('lastActivity') }};

                // Calculate time difference
                var currentTime = Math.floor(Date.now() / 1000);

                var elapsedTime = currentTime - lastActivity;

                // Check if elapsed time is greater than session timeout
                if (elapsedTime > sessionTimeout) {
                    return true;
                } else {
                    return false;
                }
            } else {
                return true;
            }
        }


        // Function to reload the page automatically
        function reloadPageOnSessionTimeout() {
            if (isSessionTimeout()) {
                setTimeout(function() {
                    window.location.reload();
                }, 2000);
            }
        }

        setInterval(reloadPageOnSessionTimeout, 30000);
        $('#menu_learners_link').on('click', function() {
            table_learner.state.clear();
        });
    </script>
    <div class="float-right d-none d-sm-inline">

    </div>
    <strong>&copy; {{ date('Y') }} <a href="{{ url('/') }}">{{ config('app.name') }}</a>. All rights reserved.</strong>
</footer>
<div id="loader_section">
    <div id="loader">
        <div id="spinner"></div>
    </div>
</div>

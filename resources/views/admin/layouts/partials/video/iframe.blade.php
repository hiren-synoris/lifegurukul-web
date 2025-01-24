@php
    use App\Models\Media;
    $responseObj = getVideoTokenData($videoId);
    $otp ="";
    $playbackInfo ="";
    // dump()
    if(isset($responseObj->otp) != false) {
        $otp = $responseObj->otp;
    }
    if(isset($responseObj->playbackInfo) != false) {
        $otp = $responseObj->playbackInfo;
    }
    $preview;
    $asset_type = -1;
    try {
        $isAssetTypeVideo = Media::where('videoId', $videoId)
            ->whereIn('asset_type', [0, 1])
            ->first();
        if (!empty($isAssetTypeVideo)) {
            $asset_type = 0;
        }
        //code...
    } catch (\Throwable $th) {
        //throw $th;
        $asset_type = -1;
    }
    $node_servers = env('NODE_SERVER_URL');
@endphp

@if (isset($preview) && !empty($preview) && $preview == 1)
    {{-- This is only for Frontend > Course preview page --}}

    <div style="position:relative;">

        <iframe id="videoPlayer"
            src="https://player.vdocipher.com/v2/?otp={{ @$responseObj->otp }}&playbackInfo={{ @$responseObj->playbackInfo }}&loop=false"
            style="border:0;max-width:100%;top:0;left:0;height:500px;width:100%;" allowfullscreen="true"
            allow="encrypted-media">
        </iframe>
        <div id="videoContainersssss"></div>
    </div>

    <script src="https://player.vdocipher.com/v2/api.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/socket.io/4.3.2/socket.io.min.js"></script>
    <script>
        const iframe = document.querySelector("iframe");
        const player = VdoPlayer.getInstance(iframe);
        var seek_to_time = '{{ $watched_time }}'; // get watched_time from DB
        const socket_urls = '<?php echo $node_servers; ?>';
        // const socket = io(socket_url+':3000');
        const sockets = io(socket_urls);
        console.log(socket_urls);
        sockets.on('connect', function() {
            console.log('Socket is running');
        });
        sockets.on('connect_error', function() {
            var videoCipher = document.getElementById('videoPlayer');
            // console.log(videoCipher);
            if (videoCipher) {
                videoCipher.style.display = 'none'; // Hide the YouTube player

                var errorMessage = document.createElement('div');
                errorMessage.className = 'error-message';
                errorMessage.textContent = 'Socket is not running';
                errorMessage.style.height = '400px';

                var videoContainer = document.getElementById('videoContainersssss');
                if (videoContainer) {
                    videoContainer.innerHTML = '';
                    videoContainer.appendChild(errorMessage);
                }

            }
        });

        $('#flexSwitchCheckChecked').change(function () {
            var loop = $(this).prop('checked') ? 'true' : 'false';
            var src = $('#videoPlayer').attr('src');
            var updatedSrc = src.replace(/loop=(true|false)/, 'loop=' + loop);
            $('#videoPlayer').attr('src', updatedSrc);
        });

        // All DOM API supports on player.videoloop
        // player.video.addEventListener("play", function () {
        // //   console.log("Video is playing");
        // });

        // player.video.addEventListener("pause", function () {
        //   var totalVideoDuration = Math.floor(player.video.duration);
        //   videoCurrentTime =  Math.floor(player.video.currentTime);
        //   updateProgressData(videoCurrentTime);
        // });

        player.video.addEventListener("ended", function() {
            //   console.log("Video is ended");
            $('.next').click();
        });

        // Start video where user left
        player.video.addEventListener('seeking', function(e) {

        });

        var flag = 0;
        var isCompleted = $("#is_completed").val();
        if (isCompleted == '') {
            isCompleted = 0;
        }
        player.video.addEventListener("timeupdate", function() {
            // console.log("sdfsdf");
            var totalVideoDuration = Math.floor(player.video.duration);
            var videoCurrentTime = Math.floor(player.video.currentTime);
            var limitDuration = (totalVideoDuration / 100) * 95; // Video completed 95%
            if (videoCurrentTime >= limitDuration) {
                isCompleted = 1;
                updateProgressData(totalVideoDuration, videoCurrentTime, isCompleted);
                $("#is_completed").val(1);
                // var user_course_id = "{{ isset($user_course_id ) ? $user_course_id  : ''}}"
                // $.ajax({
                //     url: "{{ route('user_course_update') }}",
                //     type: "get",
                //     data: {user_course_id:user_course_id},

                // });

            } else {
                updateProgressData(totalVideoDuration, videoCurrentTime);
                // Call first time while click on 'Play' button.
                //   if(flag == 0){
                //     updateProgressData(totalVideoDuration, videoCurrentTime, isCompleted);
                //       flag = 1;
                //   }
            }
        });
    </script>
@else
    {{-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> --}}
    {{-- This is only for Backend Course builder page --}}
    @if ($asset_type == 0 || $asset_type == 1)
        <div id="embedBox" style="width:1280px;max-width:100%;height:650px;">
            <iframe
                src="https://player.vdocipher.com/v2/?otp={{ @$responseObj->otp }}&playbackInfo={{ @$responseObj->playbackInfo }}"
                style="border:0;max-width:100%;top:0;left:0;height:650px;width:100%;" allowfullscreen="true"
                allow="encrypted-media">
            </iframe>
        </div>
        {{-- <div id="embedBoxPending" style="width:1280px;max-width:100%;height:auto;margin-top:50px;">
            <div id="loader_section_video_pending">
                <div id="loader" style="position: inherit;transform: inherit;padding:150px;">
                    <div id="spinner"></div>
                    <span class="msg">Uploading on server</span>
                </div>
            </div>
        </div> --}}

        <script src="https://player.vdocipher.com/v2/api.js"></script>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        {{-- @else
        <div id="embedBox" style="width:1280px;max-width:100%;height:auto;"></div> --}}
        {{-- @endif
    @if ($asset_type == 0) --}}
        <?php /*<script>
            var intid = null;
            // (function(v, i, d, e, o) {
            //     v[o] = v[o] || {};
            //     v[o].add = v[o].add || function V(a) {
            //         (v[o].d = v[o].d || []).push(a);
            //     };
            //     if (!v[o].l) {
            //         v[o].l = 1 * new Date();
            //         a = i.createElement(d), m = i.getElementsByTagName(d)[0];
            //         a.async = 1;
            //         a.src = e;
            //         m.parentNode.insertBefore(a, m);
            //     }
            // })(window, document, "script", "https://player.vdocipher.com/playerAssets/1.6.10/vdo.js", "vdo");
            // vdo.add({
            //     otp: "<?php echo $otp; ?>",
            //     playbackInfo: "<?php echo $playbackInfo; ?>",
            //     theme: "9ae8bbe8dd964ddc9bdb932cca1cb59a",
            //     container: document.querySelector("#embedBox"),
            // });
            // if($("#embedBox").hasClass('d-none')){
            // sendAjax(); // for page refresh
            // }
            clearInterval(intid);
            var flag = 0;

            function sendAjax() {

                $.ajax({
                    beforeSend: function(xhr) {
                        console.log('before called');
                        xhr.setRequestHeader("Accept", "application/json");
                    },
                    url: "{{ route('video.status', ['videoid' => $videoId]) }}",
                    method: 'GET',
                    success: function(res, textStatus, xhr) {

                        if (res) {
                            console.log('if');
                            $("#loader_section_video_pending").hide();
                            $("#embedBoxPending").hide();
                            $("#embedBox").removeClass('d-none');
                            // clearInterval(intid);
                            cint(intid);
                        } else {
                            console.log('else');
                            $("#embedBoxPending").show();
                            $("#embedBox").addClass('d-none');
                        }

                    }
                });
            }

            function onVdoCipherAPIReady(vdo) {
                // api is ready to use
                // var tota_duration = 0;
                // get reference to player
                // var v = vdo.getObjects()[0];
                // flag = 0;
                intid = setInterval(function() {
                    sendAjax();
                    // tota_duration = v._metaData.duration;
                    // $("#duration").val(Math.floor(tota_duration)); // For display purpose h:i:s.
                    // $('#duration_in_seconds').val(Math.floor(tota_duration)); // For store data in seconds.

                    // // document.querySelector("#td").value = v._metaData.duration;
                    // // ajax call for 90% complete
                    // var limitDuration = (tota_duration / 100) * 90;
                    // if (v.totalCovered >= limitDuration && flag == 0) {
                    //     // console.log("90% completed");
                    //     flag = 1;
                    // }
                    // document.querySelector("#tp").value = v.totalPlayed;
                    // document.querySelector("#tc").value = v.totalCovered;
                }, 10000);
            }

            function cint() {
                if (intid) clearInterval(intid);
            }
        </script> */ ?>
    @endif
@endif

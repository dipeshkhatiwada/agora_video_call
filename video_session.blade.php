<!DOCTYPE html>
<html lang="en">

<head>
    <title>Counselling Video Chat</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
<div class="container-fluid p-0">
    <div id="main-container">
        <div class="row">
            <div class="col-10">


        <div id="screen-share-btn-container" class="col-2 float-right text-right mt-2">
            <button id="screen-share-btn"  type="button" class="btn btn-lg">
                <i id="screen-share-icon" class="fas fa-share-square"></i>
            </button>
        </div>
        <div id="buttons-container" class="row justify-content-center mt-3">
            <div class="col-md-2 text-center">
                <button id="mic-btn" type="button" class="btn btn-block btn-dark btn-lg">
                    <i id="mic-icon" class="fas fa-microphone"></i>
                </button>
            </div>
            <div class="col-md-2 text-center">
                <button id="video-btn"  type="button" class="btn btn-block btn-dark btn-lg">
                    <i id="video-icon" class="fas fa-video"></i>
                </button>
            </div>
            <div class="col-md-2 text-center">
                <button id="exit-btn"  type="button" class="btn btn-block btn-danger btn-lg">
                    <i id="exit-icon" class="fas fa-phone-slash"></i>
                </button>
            </div>
        </div>
        <div id="full-screen-video"></div>
        <div id="lower-video-bar" class="row fixed-bottom mb-1">
            <div id="remote-streams-container" class="container col-9 ml-1">
                <div id="remote-streams" class="row">
                    <!-- insert remote streams dynamically -->
                </div>
            </div>
            <div id="local-stream-container" class="col p-0">
                <div id="mute-overlay" class="col">
                    <i id="mic-icon" class="fas fa-microphone-slash"></i>
                </div>
                <div id="no-local-video" class="col text-center">
                    <i id="user-icon" class="fas fa-user"></i>
                </div>
                <div id="local-video" class="col p-0"></div>
            </div>
        </div>
    </div>
            <div class="col-2" style="background: #fff;">
                <div><i class="fa fa-eye"></i> <span id="visitorCount"></span></div>
                <ul id="user_viewer_">
                </ul>
            </div>
        </div>
    </div>
</div>
<!-- Modal -->
<div class="modal fade" id="modalForm">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header text-center">
                <h4 class="modal-title w-100 font-weight-bold">Join Channel</h4>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body mx-3">
                <h6 class="modal-title w-100 font-weight-bold">Allow mic and camera access</h6>
                <input type="hidden" id="form-appid" class="form-control" value="fc2e17576a5247679108661a0ef3e7dc">
                <input type="hidden" id="form-url" class="form-control" value="{{route('employee.counselling_booking.details',$data['counselling_booking']->id)}}">
                <input type="hidden" id="form-channel" class="form-control" value="{{ $data['channel']}}">
                <input type="hidden" id="pusher_key" value="{{'e82a1bf369a704f763a5'}}">


            </div>
            <div class="modal-footer d-flex justify-content-center">
                <button id="join-channel" class="btn btn-default">Join Channel</button>
            </div>
        </div>
    </div>
</div>
</body>
<script src="{{asset('assets/agora_call/AgoraRTCSDK-3.1.1.js')}}"></script>
<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.0/css/all.css" integrity="sha384-lZN37f5QGtY3VHgisS14W3ExzMWZxybE1SJSEsQp9S+oqd12jhcu+A56Ebc1zFSJ" crossorigin="anonymous">
<link href="https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/css/bootstrap.min.css" rel="stylesheet">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/js/bootstrap.min.js"></script>
<script src="https://js.pusher.com/6.0/pusher.min.js"></script>
<script type="text/javascript">
    $("#mic-btn").prop("disabled", true);
    $("#video-btn").prop("disabled", true);
    $("#screen-share-btn").prop("disabled", true);
    $("#exit-btn").prop("disabled", true);

    $(document).ready(function(){
        $("#modalForm").modal("show");
    });
</script>
<script>
    var pusher_key = $('#pusher_key').val();
    $(document).ready(function() {
        // alert('sa');
        // Enable pusher logging - don't include this in production
        Pusher.logToConsole = true;

        console.log("Pusher key", pusher_key);
        var pusher = new Pusher(pusher_key, {
            cluster: 'ap2'
        });

        var channel = pusher.subscribe('my-audience');
        channel.bind('my-broadcast', function(data) {
            // alert(JSON.stringify(data));
            if(data.type =='joinstream'){
                // if(data.message == 'old_user')
                // {
                //     document.getElementById("visitorCount").innerHTML=data.counter+" Viewer";
                //     $('#user_viewer_').empty();
                //     $('#user_viewer_').append(data.html);
                //
                //
                //     // // $('#visitorCount').textContent=data.viewer_count;
                //     // var li = document.getElementById('user_viewer_'+data.user_id);
                //     // console.log(li);
                //     // if( li == null)
                //     // {
                //     //     $('#user_viewer_').append(data.html);
                //     // }
                // }
                // else if(data.message == 'new_user')
                // {
                //     $('#visitorCount').val(data.viewer_count);
                //     $('#user_viewer_').append(data.html);
                //
                // }
                document.getElementById("visitorCount").innerHTML=data.counter+" Viewer";
                $('#user_viewer_').empty();
                $('#user_viewer_').append(data.html);
                // alert(data.html);
            }
            else if(data.type == 'leavestream'){
                document.getElementById("visitorCount").innerHTML=data.count+" Viewer";
                $('#user_viewer_'+data.user_id).remove();
                // $('#visitorCount').val(data.count);

            }

        });
    });

    // join channel modal
    $( "#join-channel" ).click(function( event ) {
        var agoraAppId = $('#form-appid').val();
        var channelName = $('#form-channel').val();
        console.log('app_id:'+agoraAppId);
        var csrf_token = $('meta[name="csrf-token"]').attr('content');
        $.ajax({
            type: 'post',
            headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            url: '{{url("/employee/counselling/live-stream/store-time")}}',
            data:{
                _token : csrf_token,
                'channel': channelName,
                'counselling_booking': '{{$data['counselling_booking']->id}}',
            },
            cache:false,
            success:function(datas){
                console.log(datas);
            }
        });
        initClientAndJoinChannel(agoraAppId, channelName);
        $("#modalForm").modal("hide");

    });

    // UI buttons
    function enableUiControls(localStream) {

        $("#mic-btn").prop("disabled", false);
        $("#video-btn").prop("disabled", false);
        $("#screen-share-btn").prop("disabled", false);
        $("#exit-btn").prop("disabled", false);

        $("#mic-btn").click(function(){
            toggleMic(localStream);
        });

        $("#video-btn").click(function(){
            toggleVideo(localStream);
        });

        $("#screen-share-btn").click(function(){
            console.log('test');
            toggleScreenShareBtn(); // set screen share button icon
            $("#screen-share-btn").prop("disabled",true); // disable the button on click
            if(screenShareActive){
                stopScreenShare();
            } else {
                initScreenShare();
            }
        });

        $("#exit-btn").click(function(){
            var csrf_token = $('meta[name="csrf-token"]').attr('content');
            var channelName = $('#form-channel').val();

            $.ajax({
                type: 'post',
                headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                url: '{{url("/employee/counselling/live-stream/end-time")}}',
                data:{
                    _token : csrf_token,
                    'channel': channelName,
                    'counselling_booking': '{{$data['counselling_booking']->id}}',
                },
                cache: false,
                success: function(datas){
                    console.log('Success', datas);
                    // location.replace(base_url + "/enroll/list-page/company/" + channelName);
                    console.log("so sad to see you leave the channel");
                    leaveChannel();
                }
            });


        });

        // keyboard listeners
        $(document).keypress(function(e) {
            switch (e.key) {
                case "m":
                    console.log("squick toggle the mic");
                    toggleMic(localStream);
                    break;
                case "v":
                    console.log("quick toggle the video");
                    toggleVideo(localStream);
                    break;
                case "s":
                    console.log("initializing screen share");
                    toggleScreenShareBtn(); // set screen share button icon
                    $("#screen-share-btn").prop("disabled",true); // disable the button on click
                    if(screenShareActive){
                        stopScreenShare();
                    } else {
                        initScreenShare();
                    }
                    break;
                case "q":
                    console.log("so sad to see you quit the channel");
                    leaveChannel();
                    break;
                default:  // do nothing
            }

            // (for testing)
            if(e.key === "r") {
                window.history.back(); // quick reset
            }
        });
    }

    function toggleBtn(btn){
        btn.toggleClass('btn-dark').toggleClass('btn-danger');
    }

    function toggleScreenShareBtn() {
        $('#screen-share-btn').toggleClass('btn-danger');
        $('#screen-share-icon').toggleClass('fa-share-square').toggleClass('fa-times-circle');
    }

    function toggleVisibility(elementID, visible) {
        if (visible) {
            $(elementID).attr("style", "display:block");
        } else {
            $(elementID).attr("style", "display:none");
        }
    }

    function toggleMic(localStream) {
        toggleBtn($("#mic-btn")); // toggle button colors
        $("#mic-icon").toggleClass('fa-microphone').toggleClass('fa-microphone-slash'); // toggle the mic icon
        if ($("#mic-icon").hasClass('fa-microphone')) {
            localStream.unmuteAudio(); // enable the local mic
            toggleVisibility("#mute-overlay", false); // hide the muted mic icon
        } else {
            localStream.muteAudio(); // mute the local mic
            toggleVisibility("#mute-overlay", true); // show the muted mic icon
        }
    }

    function toggleVideo(localStream) {
        toggleBtn($("#video-btn")); // toggle button colors
        $("#video-icon").toggleClass('fa-video').toggleClass('fa-video-slash'); // toggle the video icon
        if ($("#video-icon").hasClass('fa-video')) {
            localStream.unmuteVideo(); // enable the local video
            toggleVisibility("#no-local-video", false); // hide the user icon when video is enabled
        } else {
            localStream.muteVideo(); // disable the local video
            toggleVisibility("#no-local-video", true); // show the user icon when video is disabled
        }
    }
</script>

{{--<script src="{{asset('assets/agora_call/ui.js')}}"></script>--}}
<script src="{{asset('assets/agora_call/agora-interface.js')}}"></script>
<link rel="stylesheet" type="text/css" href="{{asset('assets/agora_call/style.css')}}"/>
</html>

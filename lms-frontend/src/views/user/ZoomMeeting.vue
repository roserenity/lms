<template>
    <div class="iframe-container">
        <meta charset="utf-8" />
        <link
            type="text/css"
            rel="stylesheet"
            href="https://source.zoom.us/2.8.0/css/bootstrap.css"
        />
        <link
            type="text/css"
            rel="stylesheet"
            href="https://source.zoom.us/2.8.0/css/react-select.css"
        />
        <meta
            name="viewport"
            content="width=device-width, initial-scale=1"
        />
    </div>
</template>

<script>
import { mapActions, mapState } from 'vuex'
import { ZoomMtg } from '@zoomus/websdk'
import { generateMeetingSDK } from '../../services/jsrsasign'
import $ from 'jquery'

ZoomMtg.setZoomJSLib('https://source.zoom.us/2.8.0/lib', '/av');
ZoomMtg.preLoadWasm();
//ZoomMtg.prepareJssdk()
ZoomMtg.prepareWebSDK();
ZoomMtg.i18n.load('en-US')
ZoomMtg.i18n.reload('en-US')


export default {
    mounted(){
        let course_id = this.$route.params.id
        
        let _key = null

        this.getMeetingAction(this.$route.params.mn).then(()=>{
            if(!this.meeting) {
                top.window.location.href = `/course/${this.$route.params.id}`
            }
            let _signature = generateMeetingSDK(this.meeting.id, 0)

            document.getElementById("zmmtg-root").style.display = "block"

            ZoomMtg.init({
                meetingInfo: [ // optional
                    'topic',
                    'host',
                    'mn',
                    'participant',
                    'dc',
                    'enctype',
                    'report'
                ],
                leaveUrl: `${window.location.protocol}//${window.location.host}/course/${this.$route.params.id}/meeting/leave`,
                success: (success) => {
                    ZoomMtg.join({
                        sdkKey: process.env.VUE_APP_ZOOM_SDK_KEY,
                        signature: _signature,
                        meetingNumber: this.meeting.id,
                        userName: (this.user.first_name && this.user.last_name) ? this.user.first_name +' '+ this.user.last_name : 'User',
                        userEmail: this.user.email,
                        passWord: this.meeting.password,
                        success: (success) => {
                            console.log(success);
                            document.querySelector('[aria-label="open the chat pane"]').addEventListener('click', function(){
                                let sendButton = document.createElement('button');
                                sendButton.textContent = 'Send';
                                sendButton.id = 'chatSendButton'
                                sendButton.className = 'btn-default sendButton'
                                sendButton.type = 'submit'
                                setTimeout(()=>{
                                    document.getElementById("chat-window").append(sendButton);
                                    sendButton.addEventListener('click', ()=>{
                                        // let textarea = document.getElementsByClassName("chat-box__chat-textarea window-content-bottom")[0]
                                        // textarea.focus()
                                        // console.log(textarea.textContent)
                                        // textarea.addEventListener('keypress', (event)=>{
                                        //     console.log(event)
                                        //     textarea.value += event.key;
                                        // });
                                        //window.dispatchEvent(new KeyboardEvent('keypress', {'key': 'Enter', 'code': 'Enter'}));

                                    });

                                    $('textarea').on('keydown', function(e){
                                        console.log(e === _key)
                                        if(e.originalEvent.key === 'Enter')_key = e
                                    });

                                    var e = $.Event("keypress");
                                    e.which = 13;
                                    $("textarea").focus().val('test').trigger(e);

                                    $('#chatSendButton').click(function() {
                                        var e = $.Event("keydown");
                                        e.which = 13;
                                        e.altKey = false
                                        e.bubbles = true
                                        e.cancelable = true
                                        e.charCode = 0
                                        e.code = 'Enter'
                                        e.ctrlKey = false
                                        e.detail = 0
                                        e.eventPhase = 0
                                        e.key = "Enter"
                                        e.keyCode = 13
                                        e.metaKey = false
                                        e.shiftKey = false

                                        $("textarea").focus().trigger(_key);
                                    })
                                    //sendButton.onclick = function(){
                                        // var event = new KeyboardEvent('keypress', {
                                        //     code: 'Enter',
                                        //     key: 'Enter',
                                        //     charCode: 13,
                                        //     keyCode: 13,
                                        //     altKey: false
                                        // });
                                        // let textarea = document.getElementsByClassName("chat-box__chat-textarea window-content-bottom")[0]
                                        // textarea.focus()
                                        // textarea.value += "GeeksforGeeks"
                                        
                                        // document.querySelector('textarea').dispatchEvent(new KeyboardEvent('keypress', { 'key': 'a'}))


                                        // const keyPressKey2   = new KeyboardEvent('keypress', {key: '2', code: 'Digit2'});

                                        // textarea.dispatchEvent(keyPressKey2);
                                    //}
                                }, 1000)
                            })
                        },
                        error: (error) => {
                            console.log(error);
                        }
                    });
                },
                error: (error) => {
                    console.log(error);
                }
            });

        })
    },
    computed: {
        ...mapState({
            user: (state) => state.user
        }),
        ...mapState('usr', {
            meeting: (state) => state.meeting
        })
    },
    methods: {
        ...mapActions('usr',['getMeetingAction']),
    }
}
</script>

<style>
#zmmtg-root {
  display: none;
}

#content_container {
    overflow: hidden !important;
}

@media only screen and (max-width: 768px) {
    .ReactModal__Content.ReactModal__Content--after-open {
        width: 98vw !important;
        display: flex !important;
    }
    #chatReceiverMenu {
        font-size: 10px !important;
    }
    .sendButton {
        background: #2d8cff !important;
        border-radius: 5px !important;
        padding: 5px 10px  !important;
        right: 0 !important;
        color: white !important;
        margin-left: 80% !important;
        margin-bottom: 10px;
    }
}
</style>
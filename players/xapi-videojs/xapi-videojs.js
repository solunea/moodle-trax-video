let videoEventListener;

(function (ADL) {
    ADL.XAPIVideoJS = function (target) {
        let myPlayer = videojs(target);

        let objectID = activityIri;
        let sessionID = ADL.ruuid();
        let sendCCSubtitle = false;
        let actor = JSON.parse(ADL.XAPIWrapper.lrs.actor);

        videoEventListener = new VideoEventListener(myPlayer, objectID, sessionID, sendCCSubtitle, actor);
    };
}(window.ADL = window.ADL || {}));

const VIDEO_STATE_NOTSTARTED = 0;
const VIDEO_STATE_PLAYING = 1;
const VIDEO_STATE_INPAUSE = 2;
const VIDEO_STATE_ENDED = 3;

class Video {
    state;
    duration; // Duration cannot be known before the video loading.
    completionState;
    completionMarginInSeconds = 5; // We add 5 seconds when comparing total of viewed segments to duration, because of some glitches when pausing and playing lightly shifts the position.

    constructor() {
        this.state = VIDEO_STATE_NOTSTARTED;
        this.completionState = new CompletionState();
    }

    isCompleted() {
        // TODO : problem : this function compares the total time of viewed segments to the total duration, but should check if the viewed segments cover the whole video!
        if (isNaN(this.duration)) {
            console.log("Duration is NAN");
            return false;
        } else {
            console.log("Viewed=" + this.completionState.getViewedDuration() + " Duration=" + this.duration);
            return this.completionState.getViewedDuration() + this.completionMarginInSeconds > this.duration;
        }
    }

    /**
     *
     * @returns {number} the percentage of the video viewed, compared to the total duration, percentage 0->1.
     */
    getCompletionPercentage() {
        let comparedDuration = this.duration;
        if (comparedDuration > this.completionMarginInSeconds) {
            comparedDuration = comparedDuration - this.completionMarginInSeconds;
        }
        let progress = 1 * (this.completionState.getViewedDuration() / comparedDuration).toFixed(2);
        if (progress > 1) {
            progress = 1;
        }
        return progress;
    }
}

class CompletionState {
    // start1[.]end1[,]start2[.]end2
    viewedSegments = "";
    position = 0;

    /**
     *  Sum of duration of viewed segments with total video duration.
     * @returns {float}
     */
    getViewedDuration() {
        // TODO : problem : we do not check if segments are sumed up even if the're overlaping on other segments.
        // Possible values :
        // startX[.]  -> must add current position as end
        // startX[.]endX

        if (this.viewedSegments === "") {
            return false;
        }

        let currentViewedSegments = this.viewedSegments;
        if (currentViewedSegments.endsWith("[.]")) {
            currentViewedSegments += this.position;
        }

        let segmentsDuration = 0.0;
        let segment = [];
        currentViewedSegments.split("[,]").forEach(function (v, i) {
            segment[i] = v.split("[.]");
            if (parseFloat(segment[i][1]) > parseFloat(segment[i][0])) {
                if (parseFloat(segment[i][1]) > parseFloat(segment[i][0])) {
                    segmentsDuration += (parseFloat(segment[i][1]) - parseFloat(segment[i][0]));
                }
            }
        });
        return segmentsDuration;
    }

    startSegment(position) {
        if (this.viewedSegments !== "") {
            this.viewedSegments += "[,]";
        }
        this.viewedSegments += position + "[.]";
    }

    endSegment(position) {
        this.viewedSegments += +position;
    }
}

/**
 * Listen for video player events : change the Video object (video state and completion status), and calls VideoProfileListener.onVideoUpdate()
 */
class VideoEventListener {
    /**
     * The videojs object
     */
    videoPlayer;
    /**
     * The Video object
     */
    video;
    videoProfileListener;

    constructor(videoPlayer, objectID, sessionID, sendCCSubtitle, actor) {
        let self = this;
        this.videoPlayer = videoPlayer;
        this.video = new Video();
        this.video.textTracks = videoPlayer.textTracks();
        this.videoProfileListener = new VideoProfileListener(this.video, videoPlayer, objectID, sessionID, sendCCSubtitle, actor);
        videoPlayer.on("timeupdate", function () {
            console.log("==timeupdate==  current=" + formatFloat(self.videoPlayer.currentTime()) + "  position=" + self.video.completionState.position);
            // If the timeupdate is more than 1 second, it's not a normal play increment, it's a user interaction to move elsewhere
            let currentPosition = formatFloat(self.videoPlayer.currentTime());
            if (Math.abs(currentPosition - self.video.completionState.position) > 1) {
                self.video.completionState.endSegment(self.video.completionState.position);
                self.video.completionState.startSegment(currentPosition);
            } else {
                self.video.completionState.position = currentPosition;
            }
            // We do not test completion, because it may be sent with incomplet segments and position: completed will be tested on pause (=by user or by end)
        });
        videoPlayer.on("seeked", function () {
            console.log("==seeked==  current=" + formatFloat(self.videoPlayer.currentTime()) + "  position=" + self.video.completionState.position);
            switch (self.video.state) {
                case VIDEO_STATE_NOTSTARTED:
                    console.log("VideoEventListener : no action on 'Seeked', as it was not played yet.");
                    break;
                case VIDEO_STATE_PLAYING:
                    self.video.completionState.endSegment(self.video.completionState.position);
                    self.video.completionState.startSegment(formatFloat(self.videoPlayer.currentTime()));
                    break;
                case VIDEO_STATE_INPAUSE:
                    self.video.completionState.endSegment(self.video.completionState.position);
                    self.video.completionState.startSegment(formatFloat(self.videoPlayer.currentTime()));
                    break;
                case VIDEO_STATE_ENDED:
                    self.video.completionState.endSegment(self.video.completionState.position);
                    self.video.completionState.startSegment(formatFloat(self.videoPlayer.currentTime()));
                    self.video.completionState.position = formatFloat(self.videoPlayer.currentTime());
                    break;
                default:
                    console.log("VideoPlayer 'Seeked' event not treated, because current video object state is " + self.video.completionState);
            }
        });
        videoPlayer.on("seeking", function () {
            console.log("==seeking==  current=" + formatFloat(self.videoPlayer.currentTime()) + "  position=" + self.video.completionState.position);
            switch (self.video.state) {
                case VIDEO_STATE_NOTSTARTED:
                    console.log("VideoEventListener : no action on 'Seeking', as it was not played yet.");
                    break;
                case VIDEO_STATE_PLAYING:
                    self.video.completionState.position = formatFloat(self.videoPlayer.currentTime());
                    break;
                case VIDEO_STATE_INPAUSE:
                    self.video.completionState.position = formatFloat(self.videoPlayer.currentTime());
                    self.video.completionState.startSegment(formatFloat(self.videoPlayer.currentTime()));
                    break;
                case VIDEO_STATE_ENDED:
                    // Videod ended, but user click on "replay" -> seeking
                    self.video.state = VIDEO_STATE_PLAYING;
                    self.video.completionState.startSegment(formatFloat(self.videoPlayer.currentTime()));
                    self.videoProfileListener.played();
                    break;
                default:
                    console.log("VideoPlayer 'Paused' event not treated, because current video object state is " + self.video.completionState);
            }
        });
        videoPlayer.on("ended", function () {
            console.log("==ended==");
            switch (self.video.state) {
                case VIDEO_STATE_NOTSTARTED:
                    console.log("VideoEventListener : no action on 'Ended', as it was not played yet.");
                    break;
                case VIDEO_STATE_PLAYING:
                    self.video.state = VIDEO_STATE_ENDED;
                    self.video.completionState.endSegment(formatFloat(self.videoPlayer.currentTime()));
                    self.video.completionState.position = formatFloat(self.video.duration);
                    if (self.video.isCompleted()) {
                        self.videoProfileListener.completed();
                    }
                    break;
                case VIDEO_STATE_INPAUSE:
                    self.video.state = VIDEO_STATE_ENDED;
                    self.video.completionState.position = formatFloat(self.video.duration);
                    if (self.video.isCompleted()) {
                        self.videoProfileListener.completed();
                    }
                    break;
                case VIDEO_STATE_ENDED:
                    console.log("VideoEventListener : no action on 'Ended', as it was ended.");
                    break;
                default:
                    console.log("VideoEventListener : videoPlayer 'Ended' event not treated, because current video object state is " + self.video.completionState);
            }
        });
        videoPlayer.on("play", function () {
            console.log("==play==");
            switch (self.video.state) {
                case VIDEO_STATE_NOTSTARTED:
                    // First start event, the video wasn't played yet
                    self.video.state = VIDEO_STATE_PLAYING;
                    self.video.completionState.startSegment(0);
                    self.video.completionState.position = 0;
                    self.video.duration = formatFloat(videoPlayer.duration());
                    self.videoProfileListener.initialized();
                    self.videoProfileListener.played();
                    if (self.video.isCompleted()) {
                        self.videoProfileListener.completed();
                    }
                    break;
                case VIDEO_STATE_PLAYING:
                    console.log("VideoEventListener : no action on 'Played', as it was already playing.");
                    break;
                case VIDEO_STATE_INPAUSE:
                    self.video.state = VIDEO_STATE_PLAYING;
                    self.video.completionState.startSegment(formatFloat(self.videoPlayer.currentTime()));
                    self.video.completionState.position = formatFloat(self.videoPlayer.currentTime());
                    self.videoProfileListener.played();
                    if (self.video.isCompleted()) {
                        self.videoProfileListener.completed();
                    }
                    break;
                case VIDEO_STATE_ENDED:
                    self.video.state = VIDEO_STATE_PLAYING;
                    self.video.completionState.startSegment(formatFloat(self.videoPlayer.currentTime()));
                    self.video.completionState.position = formatFloat(self.videoPlayer.currentTime());
                    self.videoProfileListener.played();
                    if (self.video.isCompleted()) {
                        self.videoProfileListener.completed();
                    }
                    break;
                default:
                    console.log("VideoEventListener : videoPlayer 'Played' event not treated, because current video object state is " + self.video.completionState);
            }
        });
        videoPlayer.on("pause", function () {
            console.log("==pause==");
            switch (self.video.state) {
                case VIDEO_STATE_NOTSTARTED:
                    console.log("VideoEventListener : no action on 'Pause' event, as it was not played yet.");
                    break;
                case VIDEO_STATE_PLAYING:
                    self.video.state = VIDEO_STATE_INPAUSE;
                    self.video.completionState.endSegment(formatFloat(self.videoPlayer.currentTime()));
                    self.video.completionState.position = formatFloat(self.videoPlayer.currentTime());
                    self.videoProfileListener.paused();
                    if (self.video.isCompleted()) {
                        self.videoProfileListener.completed();
                    }
                    break;
                case VIDEO_STATE_INPAUSE:
                    console.log("VideoEventListener : no action, as it was already paused.");
                    break;
                case VIDEO_STATE_ENDED:
                    console.log("VideoEventListener : no action, as it cannot be ended while paused.");
                    break;
                default:
                    console.log("VideoPlayer 'Paused' event not treated, because current video object state is " + self.video.completionState);
            }
        });
        videoPlayer.on("fullscreenchange", function () {
            console.log("==fullscreenchange==");
            self.videoProfileListener.fullScreenChange();
        });
        videoPlayer.on("volumechange", function () {
            console.log("==volumechange==");
            self.videoProfileListener.volumeChange();
        });
    }
}

const STATEMENT_INITIALIZED = "initalized";
const STATEMENT_PAUSED = "paused";
const STATEMENT_PLAYED = "played";
const STATEMENT_COMPLETED = "completed";
const STATEMENT_TERMINATED = "terminated";
const STATEMENT_INTERACTED = "interacted";

/**
 * The Terminate xAPI statement is sent when the user clicks on "Terminate video" button. The event is sent, and the video is disposed (cannot be viewed again).
 * @type {number}
 */
const TERMINATE_STRATEGY_ONACTION = 0;
/**
 * The Terminate xAPI statement is sent when the video is completed. The video can continue to be played, but no more statement will be sent.
 * @type {number}
 */
const TERMINATE_STRATEGY_ONCOMPLETED_ANDCONTINUE = 1;
/**
 * The Terminate xAPI statement is sent when the video is completed. The videois disposed, and cannot be played without a new initializaion.
 * @type {number}
 */
const TERMINATE_STRATEGY_ONCOMPLETED_ANDSTOP = 2;

class VideoProfileListener {
    video;
    videoPlayer;
    objectID;
    sessionID;
    sendCCSubtitle;
    actor;
    completionSent = false;
    terminatedSent = false;
    lastSentStatement = "";
    // TODO retrieve this information from activity configuration (parameter in Trax Video activity)
    terminateStrategy = TERMINATE_STRATEGY_ONACTION;

    constructor(video, videoPlayer, objectID, sessionID, sendCCSubtitle, actor) {
        this.video = video;
        this.videoPlayer = videoPlayer;
        this.objectID = objectID;
        this.sessionID = sessionID;
        this.sendCCSubtitle = sendCCSubtitle;
        this.actor = actor;

        if (this.terminateStrategy === TERMINATE_STRATEGY_ONACTION) {
            document.getElementById('terminate_video_form').style.visibility = 'visible';
        }
    }

    initialized() {
        if (!this.terminatedSent) {
            console.log("xAPI event sending : Initialized.");
            // get the current date and time and throw it into a variable for xAPI timestamp
            let dateTime = new Date();
            let timeStamp = dateTime.toISOString();

            // check to see if the player is in fullscreen mode
            let fullScreenOrNot = this.videoPlayer.isFullscreen();

            // get the current screen size
            let screenSize = "";
            screenSize += screen.width + "x" + screen.height;

            // get the playback size of the video
            let playbackSize = "";
            playbackSize += this.videoPlayer.currentWidth() + "x" + this.videoPlayer.currentHeight();

            // get the playback rate of the video
            let playbackRate = this.videoPlayer.playbackRate();

            // vet the video length
            let length = this.video.duration;

            let ccEnabled = false;
            let ccLanguage = "";

            //Enable Captions/Subtitles
            for (let i = 0; i < this.video.textTracks.length; i++) {
                let track = textTracks[i];

                // If it is showing then CC is enabled and determine the language
                if (track.mode === 'showing') {
                    ccEnabled = true;
                    ccLanguage = track.language;
                }
            }
            // get user agent header string
            let userAgent = navigator.userAgent.toString();

            // get user volume
            let volume = formatFloat(this.videoPlayer.volume());

            // get quality
            // var quality = (myPlayer.videoHeight() < myPlayer.videoWidth())? myPlayer.videoHeight():videoWidth();/
            // ADL.XAPIWrapper.log("quality is:" + quality);

            // prepare the xAPI initialized statement
            let initializedStmt = {
                "id": this.sessionID,
                "actor": this.actor,
                "verb": {
                    "id": "http://adlnet.gov/expapi/verbs/initialized",
                    "display": {
                        "en-US": "initialized"
                    }
                },
                "object": {
                    "id": this.objectID,
                    "definition": {
                        "name": {
                            "en-US": activityTitle
                        },
                        "description": {
                            "en-US": activityDesc
                        },
                        "type": "https://w3id.org/xapi/video/activity-type/video"
                    },
                    "objectType": "Activity"
                },
                "context": {
                    "contextActivities": {
                        "category": [{
                            "id": "https://w3id.org/xapi/video"
                        }]
                    },
                    "extensions": {
                        "https://w3id.org/xapi/video/extensions/completion-threshold": "1.0",
                        "https://w3id.org/xapi/video/extensions/length": length,
                        "https://w3id.org/xapi/video/extensions/full-screen": fullScreenOrNot,
                        "https://w3id.org/xapi/video/extensions/screen-size": screenSize,
                        "https://w3id.org/xapi/video/extensions/video-playback-size": playbackSize,
                        "https://w3id.org/xapi/video/extensions/cc-enabled": ccEnabled,
                        "https://w3id.org/xapi/video/extensions/speed": playbackRate + "x",
                        "https://w3id.org/xapi/video/extensions/frame-rate": "23.98",
                        "https://w3id.org/xapi/video/extensions/quality": "960x400",
                        "https://w3id.org/xapi/video/extensions/user-agent": userAgent,
                        "https://w3id.org/xapi/video/extensions/volume": volume,
                        "https://w3id.org/xapi/video/extensions/session-id": this.sessionID

                    }
                },
                "timestamp": timeStamp
            };

            if (ccEnabled === true) {
                initializedStmt["context"]["extensions"]["https://w3id.org/xapi/video/extensions/cc-subtitle-lang"] = ccLanguage; //Add Language extention only when ccEnabled
            }

            initializedStmt = addRegistration(initializedStmt);
            //send initialized statement to the LRS & show data in console
            ADL.XAPIWrapper.log("initialized statement sent");
            ADL.XAPIWrapper.sendStatement(initializedStmt, function (resp) {
                ADL.XAPIWrapper.log("Response from LRS: " + resp.status + " - " + resp.statusText);
            });
            this.lastSentStatement = STATEMENT_INITIALIZED;
            ADL.XAPIWrapper.log(initializedStmt);
        }
    }

    played() {
        if (!this.terminatedSent) {
            console.log("xAPI event sending : Played. ViewedSegments=" + this.video.completionState.viewedSegments + " completed=" + this.video.isCompleted());
            let length = this.video.duration;

            // get the current date and time and throw it into a variable for xAPI timestamp
            let dateTime = new Date();
            let timeStamp = dateTime.toISOString();

            // get the current time position in the video
            let resultExtTime = formatFloat(this.video.completionState.position);

            let playedStmt = {
                "actor": this.actor,
                "verb": {
                    "id": "https://w3id.org/xapi/video/verbs/played",
                    "display": {
                        "en-US": "played"
                    }
                },
                "object": {
                    "id": this.objectID,
                    "definition": {
                        "name": {
                            "en-US": activityTitle
                        },
                        "description": {
                            "en-US": activityDesc
                        },
                        "type": "https://w3id.org/xapi/video/activity-type/video"
                    },
                    "objectType": "Activity"
                },
                "result": {
                    "extensions": {
                        "https://w3id.org/xapi/video/extensions/time": resultExtTime,
                    }
                },
                "context": {
                    "contextActivities": {
                        "category": [{
                            "id": "https://w3id.org/xapi/video"
                        }]
                    },
                    "extensions": {
                        "https://w3id.org/xapi/video/extensions/length": length,
                        "https://w3id.org/xapi/video/extensions/session-id": this.sessionID

                    }
                },
                "timestamp": timeStamp
            };

            playedStmt = addRegistration(playedStmt);
            //send played statement to the LRS
            ADL.XAPIWrapper.log("played statement sent");
            ADL.XAPIWrapper.sendStatement(playedStmt, function (resp) {
                ADL.XAPIWrapper.log("Response from LRS: " + resp.status + " - " + resp.statusText);
            });
            this.lastSentStatement = STATEMENT_PLAYED;
            ADL.XAPIWrapper.log(playedStmt);
        }
    }

    completed() {
        // Only sent once, and if not terminated
        if (!this.completionSent && !this.terminatedSent) {
            console.log("xAPI event sending : Completed. ViewedSegments=" + this.video.completionState.viewedSegments + " completed=" + this.video.isCompleted());
            let dateTime = new Date();
            let timeStamp = dateTime.toISOString();

            let length = this.video.duration;

            // get the progress percentage and put it in a variable called progress
            let progress = this.video.getCompletionPercentage();
            ADL.XAPIWrapper.log("video progress percentage:" + progress + ".");

            let duration = "PT" + formatFloat(this.video.completionState.getViewedDuration()).toFixed(2) + "S";

            let completedStmt = {
                "actor": this.actor,
                "verb": {
                    "id": "http://adlnet.gov/expapi/verbs/completed",
                    "display": {
                        "en-US": "completed"
                    }
                },
                "object": {
                    "id": this.objectID,
                    "definition": {
                        "name": {
                            "en-US": activityTitle
                        },
                        "description": {
                            "en-US": activityDesc
                        },
                        "type": "https://w3id.org/xapi/video/activity-type/video"
                    },
                    "objectType": "Activity"
                },
                "result": {
                    "duration": duration,
                    "completion": true,
                    "extensions": {
                        "https://w3id.org/xapi/video/extensions/time": formatFloat(this.video.completionState.position),
                        "https://w3id.org/xapi/video/extensions/progress": progress,
                        "https://w3id.org/xapi/video/extensions/played-segments": this.video.completionState.viewedSegments
                    }
                },
                "context": {
                    "contextActivities": {
                        "category": [{
                            "id": "https://w3id.org/xapi/video"
                        }]
                    },
                    "extensions": {
                        "https://w3id.org/xapi/video/extensions/session-id": this.sessionID,
                        "https://w3id.org/xapi/video/extensions/length": length,
                        "https://w3id.org/xapi/video/extensions/completion-threshold": "1.0"


                    }
                },
                "timestamp": timeStamp
            };

            completedStmt = addRegistration(completedStmt);
            //send completed statement to the LRS
            ADL.XAPIWrapper.log("completed statement sent");
            ADL.XAPIWrapper.sendStatement(completedStmt, function (resp) {
                ADL.XAPIWrapper.log("Response from LRS: " + resp.status + " - " + resp.statusText);
            });
            this.completionSent = true;
            this.lastSentStatement = STATEMENT_COMPLETED;
            ADL.XAPIWrapper.log(completedStmt);
        }
        if (this.terminateStrategy === TERMINATE_STRATEGY_ONCOMPLETED_ANDCONTINUE) {
            this.terminated();
        } else if (this.terminateStrategy === TERMINATE_STRATEGY_ONCOMPLETED_ANDSTOP) {
            this.terminated();
            this.videoPlayer.dispose();
            document.getElementById('videoMessage').innerHTML += videoIsTerminated;
        } else if (this.terminateStrategy === TERMINATE_STRATEGY_ONACTION) {
            // No call for terminated: it will be called by an action. No action after the "completed" statement is sent.
        }
    }

    terminated() {
        if (!this.terminatedSent) {
            if (this.lastSentStatement !== STATEMENT_PAUSED) {
                this.paused();
            }

            console.log("xAPI event sending : Terminated. ViewedSegments=" + this.video.completionState.viewedSegments + " completed=" + this.video.isCompleted());
            let dateTime = new Date();
            let timeStamp = dateTime.toISOString();

            // get the progress percentage and put it in a variable called progress
            let progress = this.video.getCompletionPercentage();
            ADL.XAPIWrapper.log("video progress percentage:" + progress + ".");

            let length = this.video.duration;

            let terminatedStmt = {
                "actor": this.actor,
                "verb": {
                    "id": "http://adlnet.gov/expapi/verbs/terminated",
                    "display": {
                        "en-US": "terminated"
                    }
                },
                "object": {
                    "id": this.objectID,
                    "definition": {
                        "name": {
                            "en-US": activityTitle
                        },
                        "description": {
                            "en-US": activityDesc
                        },
                        "type": "https://w3id.org/xapi/video/activity-type/video"
                    },
                    "objectType": "Activity"
                },
                "result": {
                    "extensions": {
                        "https://w3id.org/xapi/video/extensions/time": formatFloat(this.video.completionState.position),
                        "https://w3id.org/xapi/video/extensions/progress": progress,
                        "https://w3id.org/xapi/video/extensions/played-segments": this.video.completionState.viewedSegments
                    }
                },
                "context": {
                    "contextActivities": {
                        "category": [{
                            "id": "https://w3id.org/xapi/video"
                        }]
                    },
                    "extensions": {
                        "https://w3id.org/xapi/video/extensions/length": length,
                        "https://w3id.org/xapi/video/extensions/session-id": this.sessionID,
                        "https://w3id.org/xapi/video/extensions/completion-threshold": "1.0"

                    }
                },
                "timestamp": timeStamp
            };

            //send completed statement to the LRS
            ADL.XAPIWrapper.log("terminated statement sent");
            ADL.XAPIWrapper.sendStatement(terminatedStmt, function (resp) {
                ADL.XAPIWrapper.log("Response from LRS: " + resp.status + " - " + resp.statusText);
            });
            this.terminatedSent = true;
            this.lastSentStatement = STATEMENT_TERMINATED;
            ADL.XAPIWrapper.log(terminatedStmt);
        }
    }

    paused() {
        if (!this.terminatedSent) {
            console.log("xAPI event sending : Paused. ViewedSegments=" + this.video.completionState.viewedSegments + " completed=" + this.video.isCompleted());
            let dateTime = new Date();
            let timeStamp = dateTime.toISOString();

            // get the video length
            let length = this.video.duration;

            // get the current time position in the video
            let resultExtTime = formatFloat(this.video.completionState.position);

            // get the progress percentage and put it in a variable called progress
            let progress = this.video.getCompletionPercentage();
            ADL.XAPIWrapper.log("video progress percentage:" + progress + ".");

            let pausedStmt = {
                "actor": this.actor,
                "verb": {
                    "id": "https://w3id.org/xapi/video/verbs/paused",
                    "display": {
                        "en-US": "paused"
                    }
                },
                "object": {
                    "id": this.objectID,
                    "definition": {
                        "name": {
                            "en-US": activityTitle
                        },
                        "description": {
                            "en-US": activityDesc
                        },
                        "type": "https://w3id.org/xapi/video/activity-type/video"
                    },
                    "objectType": "Activity"
                },
                "result": {
                    "extensions": {
                        "https://w3id.org/xapi/video/extensions/time": resultExtTime,
                        "https://w3id.org/xapi/video/extensions/progress": progress,
                        "https://w3id.org/xapi/video/extensions/played-segments": this.video.completionState.viewedSegments
                    }
                },
                "context": {
                    "contextActivities": {
                        "category": [{
                            "id": "https://w3id.org/xapi/video"
                        }]
                    },
                    "extensions": {
                        "https://w3id.org/xapi/video/extensions/length": length,
                        "https://w3id.org/xapi/video/extensions/session-id": this.sessionID
                    }
                },
                "timestamp": timeStamp
            };

            pausedStmt = addRegistration(pausedStmt);
            //send paused statement to the LRS
            ADL.XAPIWrapper.log("paused statement sent");
            ADL.XAPIWrapper.sendStatement(pausedStmt, function (resp) {
                ADL.XAPIWrapper.log("Response from LRS: " + resp.status + " - " + resp.statusText);
            });
            this.lastSentStatement = STATEMENT_PAUSED;
            ADL.XAPIWrapper.log(pausedStmt);
        }
    }

    volumeChange() {
        if (!this.terminatedSent) {
            console.log("xAPI event sending : VolumeChange.");
            let dateTime = new Date();
            let timeStamp = dateTime.toISOString();

            // get user volume and return it as a percentage
            let isMuted = this.videoPlayer.muted();

            let volume;
            if (isMuted === true) {
                volume = 0;
            }
            if (isMuted === false) {
                volume = formatFloat(this.videoPlayer.volume());
            }

            ADL.XAPIWrapper.log("volume set to: " + volume);

            let volChangeStmt = {
                "actor": this.actor,
                "verb": {
                    "id": "http://adlnet.gov/expapi/verbs/interacted",
                    "display": {
                        "en-US": "interacted"
                    }
                },
                "object": {
                    "id": this.objectID,
                    "definition": {
                        "name": {
                            "en-US": activityTitle
                        },
                        "description": {
                            "en-US": activityDesc
                        },
                        "type": "https://w3id.org/xapi/video/activity-type/video"
                    },
                    "objectType": "Activity"
                },
                "result": {
                    "extensions": {
                        "https://w3id.org/xapi/video/extensions/time": this.video.completionState.position
                    }
                },
                "context": {
                    "contextActivities": {
                        "category": [{
                            "id": "https://w3id.org/xapi/video"
                        }]
                    },
                    "extensions": {
                        "https://w3id.org/xapi/video/extensions/session-id": this.sessionID,
                        "https://w3id.org/xapi/video/extensions/volume": volume

                    }
                },
                "timestamp": timeStamp
            };

            volChangeStmt = addRegistration(volChangeStmt);
            //send volume change statement to the LRS
            ADL.XAPIWrapper.log("interacted statement (volume change) sent");
            ADL.XAPIWrapper.sendStatement(volChangeStmt, function (resp) {
                ADL.XAPIWrapper.log("Response from LRS: " + resp.status + " - " + resp.statusText);
            });
            this.lastSentStatement = STATEMENT_INTERACTED;
            ADL.XAPIWrapper.log(volChangeStmt);
        }
    }

    fullScreenChange() {
        if (!this.terminatedSent) {
            console.log("xAPI event sending : FullScreenChange.");
            // check to see if the player is in fullscreen mode
            let isFullScreen = this.videoPlayer.isFullscreen();

            // get the current date and time and throw it into a variable for xAPI timestamp
            let dateTime = new Date();
            let timeStamp = dateTime.toISOString();

            // get the current time position in the video
            let resultExtTime = formatFloat(this.video.completionState.position);

            // get the current screen size
            let screenSize = "";
            screenSize += screen.width + "x" + screen.height;

            // get the playback size of the video
            let playbackSize = "";
            playbackSize += this.videoPlayer.currentWidth() + "x" + this.videoPlayer.currentHeight();
            //alert ("Playback Size:" + playbackSize);

            let fullScreenStmt = {
                "actor": this.actor,
                "verb": {
                    "id": "http://adlnet.gov/expapi/verbs/interacted",
                    "display": {
                        "en-US": "interacted"
                    }
                },
                "object": {
                    "id": this.objectID,
                    "definition": {
                        "name": {
                            "en-US": activityTitle
                        },
                        "description": {
                            "en-US": activityDesc
                        },
                        "type": "https://w3id.org/xapi/video/activity-type/video"
                    },
                    "objectType": "Activity"
                },
                "result": {
                    "extensions": {
                        "https://w3id.org/xapi/video/extensions/time": resultExtTime
                    }
                },
                "context": {
                    "contextActivities": {
                        "category": [{
                            "id": "https://w3id.org/xapi/video"
                        }]
                    },
                    "extensions": {
                        "https://w3id.org/xapi/video/extensions/session-id": this.sessionID,
                        "https://w3id.org/xapi/video/extensions/full-screen": isFullScreen,
                        "https://w3id.org/xapi/video/extensions/screen-size": screenSize,
                        "https://w3id.org/xapi/video/extensions/video-playback-size": playbackSize

                    }
                },
                "timestamp": timeStamp
            };

            fullScreenStmt = addRegistration(fullScreenStmt);
            //send full screen statement to the LRS
            ADL.XAPIWrapper.log("interacted statement (fullScreen change) sent");
            ADL.XAPIWrapper.sendStatement(fullScreenStmt, function (resp) {
                ADL.XAPIWrapper.log("Response from LRS: " + resp.status + " - " + resp.statusText);
            });
            this.lastSentStatement = STATEMENT_INTERACTED;
            ADL.XAPIWrapper.log(fullScreenStmt);
        }
    }
}

function addRegistration(statement) {
    if (typeof ADL.XAPIWrapper.lrs.registration == "string" && ADL.XAPIWrapper.lrs.registration.length === 36) {
        // var registration = ADL.XAPIWrapper.lrs.registration;
        if (typeof statement["context"] === undefined)
            statement["context"] = {};
        statement["context"]["registration"] = ADL.XAPIWrapper.lrs.registration;
    }
    return statement;
}

function formatFloat(number) {
    if (number == null)
        return null;
    return +(parseFloat(number).toFixed(3));
}

function terminateVideo() {
    videoEventListener.videoProfileListener.terminated();
    videoEventListener.videoPlayer.dispose();
    document.getElementById('videoMessage').innerHTML += videoIsTerminated;
    document.getElementById('terminate_video_form').style.visibility = 'hidden';
}

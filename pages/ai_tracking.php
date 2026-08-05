<?php
session_start();
if (!isset($_SESSION['user_id'])) { 
    header("Location: ../login.php"); 
    exit(); 
}
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Hand Tracking - BIM E-Learning</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- MediaPipe Hands & Camera Utils CDN -->
    <script src="https://cdn.jsdelivr.net/npm/@mediapipe/camera_utils/camera_utils.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@mediapipe/control_utils/control_utils.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@mediapipe/drawing_utils/drawing_utils.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@mediapipe/hands/hands.js" crossorigin="anonymous"></script>

    <style>
        body { background-color: #f8f9fa; }
        .video-container {
            position: relative;
            width: 100%;
            max-width: 640px;
            margin: auto;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
            background: #000;
        }
        #input_video {
            width: 100%;
            height: auto;
            transform: scaleX(-1); /* Mirror effect macam cermin */
        }
        #output_canvas {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            transform: scaleX(-1); /* Mirror effect untuk lukisan tangan juga */
        }
        .status-box {
            background: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            margin-top: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        #gesture-result {
            font-size: 2.5rem;
            font-weight: bold;
            color: #0d6efd;
            min-height: 60px;
        }
    </style>
</head>
<body>

    <nav class="navbar navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="../index.php">← Kembali ke Dashboard</a>
        </div>
    </nav>

    <div class="container mt-4">
        <h2 class="text-center mb-3">🤖 AI Hand Tracking (Proof of Concept)</h2>
        <p class="text-center text-muted">Sila benarkan akses webcam. Cuba buat isyarat <strong>A (Kepalan), 1, 2, 3, 4, atau 5 (Tapak Tangan)</strong>.</p>

        <div class="row justify-content-center">
            <div class="col-md-8">
                <!-- Paparan Webcam -->
                <div class="video-container">
                    <video id="input_video" autoplay playsinline></video>
                    <canvas id="output_canvas"></canvas>
                </div>

                <!-- Status & Result -->
                <div class="status-box">
                    <h5>Status AI:</h5>
                    <p id="ai-status" class="text-warning">Sedang memuatkan model AI...</p>
                    <hr>
                    <h5>Isyarat Dikesan:</h5>
                    <div id="gesture-result">-</div>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript untuk MediaPipe & Gesture Recognition -->
    <script>
        const videoElement = document.getElementById('input_video');
        const canvasElement = document.getElementById('output_canvas');
        const canvasCtx = canvasElement.getContext('2d');
        const statusText = document.getElementById('ai-status');
        const resultText = document.getElementById('gesture-result');

        // FUNGSI: Kira jari dan detect isyarat BIM
        function detectBIMGesture(landmarks) {
            let fingersUp = 0;

            // 1. Kira 4 jari (Index, Middle, Ring, Pinky)
            // Bandingkan koordinat Y hujung jari (Tip) dengan sendi (PIP)
            if (landmarks[8].y < landmarks[6].y) fingersUp++;  // Index
            if (landmarks[12].y < landmarks[10].y) fingersUp++; // Middle
            if (landmarks[16].y < landmarks[14].y) fingersUp++; // Ring
            if (landmarks[20].y < landmarks[18].y) fingersUp++; // Pinky

            // 2. Kira Ibu Jari (Thumb)
            // Bandingkan koordinat X hujung ibu jari (4) dengan sendi (3)
            // Logik ini direka untuk Tangan Kanan (Right Hand)
            let thumbOpen = false;
            if (landmarks[4].x < landmarks[3].x) { 
                thumbOpen = true; 
            }

            // 3. Logik Pengecaman Isyarat BIM (Mapping)
            if (fingersUp === 0 && !thumbOpen) {
                return " A (Kepalan)";
            } else if (fingersUp === 1 && landmarks[8].y < landmarks[6].y) {
                return "☝️ 1 (Satu)";
            } else if (fingersUp === 2) {
                return "✌️ 2 (Dua)";
            } else if (fingersUp === 3) {
                return "🤟 3 (Tiga)";
            } else if (fingersUp === 4 && !thumbOpen) {
                return "🖖 4 (Empat)";
            } else if (fingersUp === 4 && thumbOpen) {
                return "🖐️ 5 (Lima)";
            } else {
                return " Isyarat Lain / Belum Dikaji";
            }
        }

        // Fungsi bila MediaPipe detect tangan
        function onResults(results) {
            canvasElement.width = videoElement.videoWidth;
            canvasElement.height = videoElement.videoHeight;

            canvasCtx.save();
            canvasCtx.clearRect(0, 0, canvasElement.width, canvasElement.height);
            
            if (results.multiHandLandmarks && results.multiHandLandmarks.length > 0) {
                statusText.innerText = "✅ Tangan dikesan!";
                statusText.className = "text-success";

                for (const landmarks of results.multiHandLandmarks) {
                    // Lukis skeleton tangan
                    drawConnectors(canvasCtx, landmarks, HAND_CONNECTIONS, {color: '#00FF00', lineWidth: 3});
                    drawLandmarks(canvasCtx, landmarks, {color: '#FF0000', lineWidth: 1});

                    // Panggil fungsi detect isyarat
                    const gesture = detectBIMGesture(landmarks);
                    resultText.innerText = gesture;
                }

            } else {
                statusText.innerText = "️ Tiada tangan dikesan. Sila angkat tangan.";
                statusText.className = "text-danger";
                resultText.innerText = "-";
            }
            canvasCtx.restore();
        }

        // Setup MediaPipe Hands
        const hands = new Hands({locateFile: (file) => {
            return `https://cdn.jsdelivr.net/npm/@mediapipe/hands/${file}`;
        }});

        hands.setOptions({
            maxNumHands: 1,
            modelComplexity: 1,
            minDetectionConfidence: 0.7,
            minTrackingConfidence: 0.5
        });

        hands.onResults(onResults);

        // Setup Camera
        const camera = new Camera(videoElement, {
            onFrame: async () => {
                await hands.send({image: videoElement});
            },
            width: 640,
            height: 480
        });
        
        // Mula camera
        camera.start().then(() => {
            statusText.innerText = "✅ Kamera aktif & AI sedia!";
            statusText.className = "text-success";
        }).catch(err => {
            statusText.innerText = " Gagal akses kamera. Sila check permission browser.";
            statusText.className = "text-danger";
            console.error(err);
        });
    </script>
</body>
</html>
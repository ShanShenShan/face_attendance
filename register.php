<!DOCTYPE html>
<html>
<head>
  <title>Register Face</title>
  
  <style>
    video, canvas {
      position: absolute;
      top: 100px;
      left: 20px;
    }

    #status {
      margin-top: 20px;
      font-weight: bold;
      color: red;
    }

    #status.detected {
      color: green;
    }
  </style>
</head>
<body>
  <h2>Register Face</h2>
  <input type="text" id="name" placeholder="Name">
  <input type="email" id="email" placeholder="Email"><br><br>

  <video id="video" autoplay muted width="320" height="240"></video>
  <canvas id="overlay" width="320" height="240"></canvas>
  
  <div id="status">Detecting face...</div>
  <br>
  <button onclick="captureAndRegister()">Register</button>

    <script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>

  <script>
    const video = document.getElementById('video');
    const canvas = document.getElementById('overlay');
    const statusText = document.getElementById('status');
    const displaySize = { width: video.width, height: video.height };

    let faceDetected = false;

    async function start() {
      await Promise.all([
        faceapi.nets.tinyFaceDetector.loadFromUri('./models/tiny_face_detector'),
        faceapi.nets.faceRecognitionNet.loadFromUri('./models/face_recognition'),
        faceapi.nets.faceLandmark68Net.loadFromUri('./models/face_landmark_68')
      ]);

      const stream = await navigator.mediaDevices.getUserMedia({ video: {} });
      video.srcObject = stream;

      video.addEventListener('play', () => {
        const context = canvas.getContext('2d');
        faceapi.matchDimensions(canvas, displaySize);

        setInterval(async () => {
          const detections = await faceapi.detectAllFaces(video, new faceapi.TinyFaceDetectorOptions());
          context.clearRect(0, 0, canvas.width, canvas.height);

          if (detections.length > 0) {
            statusText.textContent = 'Face Detected!';
            statusText.classList.add('detected');
            faceDetected = true;

            const resized = faceapi.resizeResults(detections, displaySize);
            faceapi.draw.drawDetections(canvas, resized);
          } else {
            statusText.textContent = 'No Face Detected';
            statusText.classList.remove('detected');
            faceDetected = false;
          }
        }, 500);
      });
    }

    async function captureAndRegister() {
      if (!faceDetected) {
        alert("No face detected. Please try again.");
        return;
      }

      const detection = await faceapi.detectSingleFace(video, new faceapi.TinyFaceDetectorOptions()).withFaceLandmarks().withFaceDescriptor();
      if (!detection) return alert("Failed to re-detect face.");

      const descriptor = Array.from(detection.descriptor);
      const name = document.getElementById('name').value;
      const email = document.getElementById('email').value;

      if (!name || !email) return alert("Please enter name and email.");

      fetch('./api/register_face.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ name, email, descriptor })
      }).then(res => res.text()).then(alert);
    }

    start();
  </script>
</body>
</html>

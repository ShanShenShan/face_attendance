<html>
<head>
  <style>
    .video-container {
      position: relative;
      width: 320px;
      height: 240px;
      margin-top: 20px;
      margin-bottom: 20px;
    }
    .video-container video, .video-container canvas {
      position: absolute;
      top: 0;
      left: 0;
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
  <h2>Face Attendance</h2>
  <div class="video-container">
    <video id="video" autoplay width="320" height="240"></video>
    <canvas id="overlay" width="320" height="240"></canvas>
  </div>
  <div id="status">Detecting face...</div>

  <script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
  <script>
    const video = document.getElementById('video');
    const canvas = document.getElementById('overlay');
    const statusText = document.getElementById('status');
    const displaySize = { width: video.width, height: video.height };
    let labeledDescriptors = [];
    let faceDetected = false;

    async function loadLabeledDescriptors() {
      const res = await fetch('./api/get_faces.php');
      const users = await res.json();

      return users.map(user => {
        const descriptor = new Float32Array(JSON.parse(user.descriptor));
        return new faceapi.LabeledFaceDescriptors(user.id + "-" + user.name, [descriptor]);
      });
    }

    async function recognizeFace() {
      const detections = await faceapi.detectSingleFace(video, new faceapi.TinyFaceDetectorOptions()).withFaceLandmarks().withFaceDescriptor();
      const context = canvas.getContext('2d');
      context.clearRect(0, 0, canvas.width, canvas.height);
      if (!detections) {
        statusText.textContent = 'No Face Detected';
        statusText.classList.remove('detected');
        faceDetected = false;
        return;
      }
      statusText.textContent = 'Face Detected!';
      statusText.classList.add('detected');
      faceDetected = true;
      const resized = faceapi.resizeResults(detections, displaySize);
      faceapi.draw.drawDetections(canvas, resized);

      const faceMatcher = new faceapi.FaceMatcher(labeledDescriptors, 0.6);
      const bestMatch = faceMatcher.findBestMatch(detections.descriptor);

      if (bestMatch.label !== "unknown") {
        const userId = bestMatch.label.split("-")[0];
        fetch("./api/save_attendance.php", {
          method: "POST",
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ user_id: userId })
        }).then(r => r.text()).then(alert);
      }
    }

    async function start() {
      await Promise.all([
        faceapi.nets.tinyFaceDetector.loadFromUri('./models/tiny_face_detector'),
        faceapi.nets.faceRecognitionNet.loadFromUri('./models/face_recognition'),
        faceapi.nets.faceLandmark68Net.loadFromUri('./models/face_landmark_68')
      ]);
      labeledDescriptors = await loadLabeledDescriptors();
      navigator.mediaDevices.getUserMedia({ video: {} })
        .then(stream => video.srcObject = stream);

      video.addEventListener('play', () => {
        faceapi.matchDimensions(canvas, displaySize);
        setInterval(recognizeFace, 1000);
      });
    }

    start();
  </script>
</body>

</html>

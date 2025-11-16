import React, { useEffect, useRef, useState } from 'react';
import * as faceapi from 'face-api.js';
import './FaceDetectionInterview.css';

interface FaceDetectionInterviewProps {
  onReady: (isReady: boolean) => void;
  onFaceDetected: (isDetected: boolean) => void;
}

const FaceDetectionInterview: React.FC<FaceDetectionInterviewProps> = ({ onReady, onFaceDetected }) => {
  const videoRef = useRef<HTMLVideoElement>(null);
  const canvasRef = useRef<HTMLCanvasElement>(null);
  const [isModelLoading, setIsModelLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  // Load face-api models
  useEffect(() => {
    const loadModels = async () => {
      try {
        await Promise.all([
          faceapi.nets.tinyFaceDetector.loadFromUri('/models'),
          faceapi.nets.faceLandmark68Net.loadFromUri('/models'),
          faceapi.nets.faceRecognitionNet.loadFromUri('/models'),
          faceapi.nets.faceExpressionNet.loadFromUri('/models')
        ]);
        setIsModelLoading(false);
        onReady(true);
      } catch (err) {
        console.error('Failed to load models:', err);
        setError('Failed to load face detection models. Please refresh the page.');
        onReady(false);
      }
    };

    loadModels();
  }, [onReady]);

  // Start video and detect faces
  useEffect(() => {
    if (isModelLoading) return;

    const startVideo = async () => {
      try {
        const stream = await navigator.mediaDevices.getUserMedia({
          video: { width: 640, height: 480 },
          audio: false
        });
        
        if (videoRef.current) {
          videoRef.current.srcObject = stream;
        }

        const detectFaces = async () => {
          if (!videoRef.current || !canvasRef.current) return;
          
          const canvas = canvasRef.current;
          const video = videoRef.current;
          
          // Set canvas dimensions to match video
          canvas.width = video.videoWidth;
          canvas.height = video.videoHeight;
          
          const displaySize = { width: video.videoWidth, height: video.videoHeight };
          faceapi.matchDimensions(canvas, displaySize);
          
          const detection = await faceapi.detectSingleFace(
            video,
            new faceapi.TinyFaceDetectorOptions()
          ).withFaceLandmarks().withFaceExpressions();
          
          const ctx = canvas.getContext('2d');
          if (ctx) {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            
            if (detection) {
              // Draw face detection box
              const resizedDetections = faceapi.resizeResults(detection, displaySize);
              faceapi.draw.drawDetections(canvas, [resizedDetections]);
              faceapi.draw.drawFaceLandmarks(canvas, [resizedDetections]);
              
              // Update parent component about face detection
              onFaceDetected(true);
              
              // Draw face expressions
              if (detection.expressions) {
                const expressions = detection.expressions;
                const maxExpression = Object.entries(expressions).reduce((a, b) => 
                  a[1] > b[1] ? a : b
                );
                
                ctx.fillStyle = 'white';
                ctx.font = '16px Arial';
                ctx.fillText(
                  `${maxExpression[0]}: ${(maxExpression[1] * 100).toFixed(2)}%`,
                  10,
                  30
                );
              }
            } else {
              onFaceDetected(false);
            }
          }
          
          requestAnimationFrame(detectFaces);
        };
        
        video.onplay = () => {
          detectFaces();
        };
        
      } catch (err) {
        console.error('Error accessing camera:', err);
        setError('Could not access camera. Please check your permissions.');
        onReady(false);
      }
    };
    
    startVideo();
    
    return () => {
      if (videoRef.current?.srcObject) {
        const stream = videoRef.current.srcObject as MediaStream;
        stream.getTracks().forEach(track => track.stop());
      }
    };
  }, [isModelLoading, onFaceDetected]);

  if (error) {
    return (
      <div className="error-message">
        <p>{error}</p>
        <button onClick={() => window.location.reload()}>Try Again</button>
      </div>
    );
  }

  return (
    <div className="face-detection-container">
      <div className="video-container">
        <video
          ref={videoRef}
          autoPlay
          muted
          playsInline
          className="video-element"
        />
        <canvas ref={canvasRef} className="video-canvas" />
      </div>
      {isModelLoading && <div className="loading">Loading face detection models...</div>}
    </div>
  );
};

export default FaceDetectionInterview;

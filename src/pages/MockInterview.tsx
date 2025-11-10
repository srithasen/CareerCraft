import React, { useState, useRef } from 'react';
import { FaUpload, FaMicrophone, FaStop, FaPlay, FaPause, FaSpinner } from 'react-icons/fa';
import './MockInterview.css';

type InterviewStage = 'upload' | 'interview' | 'results';

const MockInterview: React.FC = () => {
  const [stage, setStage] = useState<InterviewStage>('upload');
  const [resume, setResume] = useState<File | null>(null);
  const [jobDescription, setJobDescription] = useState('');
  const [isRecording, setIsRecording] = useState(false);
  const [isPlaying, setIsPlaying] = useState(false);
  const [currentQuestion, setCurrentQuestion] = useState(0);
  const [responses, setResponses] = useState<{question: string, answer: string}[]>([]);
  const [isLoading, setIsLoading] = useState(false);
  const [score, setScore] = useState<number | null>(null);
  const [feedback, setFeedback] = useState('');
  
  const mediaRecorderRef = useRef<MediaRecorder | null>(null);
  const audioChunksRef = useRef<Blob[]>([]);
  const audioRef = useRef<HTMLAudioElement | null>(null);

  // Mock interview questions - in a real app, these would be generated based on the resume and job description
  const questions = [
    'Can you tell me about yourself?',
    'What are your greatest strengths?',
    'What is your greatest weakness?',
    'Why do you want to work at our company?',
    'Where do you see yourself in 5 years?',
  ];

  const handleResumeUpload = (e: React.ChangeEvent<HTMLInputElement>) => {
    if (e.target.files && e.target.files[0]) {
      setResume(e.target.files[0]);
    }
  };

  const startInterview = async () => {
    if (!resume || !jobDescription.trim()) {
      alert('Please upload your resume and enter a job description');
      return;
    }
    
    setIsLoading(true);
    
    // In a real app, you would send the resume and job description to an API
    // to generate personalized interview questions
    await new Promise(resolve => setTimeout(resolve, 1500));
    
    setIsLoading(false);
    setStage('interview');
    setResponses(questions.map(question => ({ question, answer: '' })));
  };

  const startRecording = async () => {
    try {
      const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
      mediaRecorderRef.current = new MediaRecorder(stream);
      audioChunksRef.current = [];
      
      mediaRecorderRef.current.ondataavailable = (event) => {
        if (event.data.size > 0) {
          audioChunksRef.current.push(event.data);
        }
      };
      
      mediaRecorderRef.current.onstop = () => {
        const audioBlob = new Blob(audioChunksRef.current, { type: 'audio/wav' });
        const audioUrl = URL.createObjectURL(audioBlob);
        
        // Update the response with the recorded audio URL
        const updatedResponses = [...responses];
        updatedResponses[currentQuestion].answer = audioUrl;
        setResponses(updatedResponses);
        
        if (audioRef.current) {
          audioRef.current.src = audioUrl;
        }
      };
      
      mediaRecorderRef.current.start();
      setIsRecording(true);
    } catch (err) {
      console.error('Error accessing microphone:', err);
      alert('Could not access microphone. Please ensure you have given microphone permissions.');
    }
  };

  const stopRecording = () => {
    if (mediaRecorderRef.current && isRecording) {
      mediaRecorderRef.current.stop();
      mediaRecorderRef.current.stream.getTracks().forEach(track => track.stop());
      setIsRecording(false);
    }
  };

  const playRecording = () => {
    if (audioRef.current) {
      audioRef.current.play();
      setIsPlaying(true);
      audioRef.current.onended = () => setIsPlaying(false);
    }
  };

  const pauseRecording = () => {
    if (audioRef.current) {
      audioRef.current.pause();
      setIsPlaying(false);
    }
  };

  const nextQuestion = () => {
    if (currentQuestion < questions.length - 1) {
      setCurrentQuestion(currentQuestion + 1);
    } else {
      // Submit interview for evaluation
      submitInterview();
    }
  };

  const submitInterview = async () => {
    setIsLoading(true);
    
    // In a real app, you would send the responses to an API for evaluation
    await new Promise(resolve => setTimeout(resolve, 2000));
    
    setScore(Math.floor(Math.random() * 40) + 60); // Random score between 60-100
    setFeedback('Your responses showed good understanding of the topics. You could improve by providing more specific examples from your experience. Try to structure your answers using the STAR method (Situation, Task, Action, Result) for behavioral questions.');
    setStage('results');
    setIsLoading(false);
  };

  const restartInterview = () => {
    setStage('upload');
    setResume(null);
    setJobDescription('');
    setResponses([]);
    setCurrentQuestion(0);
    setScore(null);
    setFeedback('');
  };

  return (
    <div className="mock-interview">
      {stage === 'upload' && (
        <div className="upload-section">
          <h1>Mock Interview Preparation</h1>
          <p>Upload your resume and enter a job description to get started with a personalized mock interview.</p>
          
          <div className="upload-container">
            <div className="upload-box">
              <FaUpload className="upload-icon" />
              <h3>Upload Your Resume</h3>
              <p>PDF, DOCX, or TXT (Max 5MB)</p>
              <input 
                type="file" 
                id="resume-upload" 
                accept=".pdf,.docx,.txt" 
                onChange={handleResumeUpload}
                className="file-input"
              />
              <label htmlFor="resume-upload" className="upload-button">
                {resume ? 'Change File' : 'Choose File'}
              </label>
              {resume && <p className="file-name">{resume.name}</p>}
            </div>
            
            <div className="job-description">
              <h3>Job Description</h3>
              <textarea
                placeholder="Paste the job description here..."
                value={jobDescription}
                onChange={(e) => setJobDescription(e.target.value)}
                rows={8}
              />
            </div>
            
            <button 
              className="start-interview-button" 
              onClick={startInterview}
              disabled={isLoading || !resume || !jobDescription.trim()}
            >
              {isLoading ? (
                <>
                  <FaSpinner className="spinner" /> Preparing Interview...
                </>
              ) : (
                'Start Mock Interview'
              )}
            </button>
          </div>
        </div>
      )}
      
      {stage === 'interview' && (
        <div className="interview-section">
          <div className="interview-header">
            <h1>Mock Interview</h1>
            <div className="progress">
              Question {currentQuestion + 1} of {questions.length}
              <div 
                className="progress-bar" 
                style={{ width: `${((currentQuestion + 1) / questions.length) * 100}%` }}
              />
            </div>
          </div>
          
          <div className="question-card">
            <h3>Question {currentQuestion + 1}:</h3>
            <p>{questions[currentQuestion]}</p>
            
            <div className="recording-section">
              <div className="recording-controls">
                {!isRecording ? (
                  <button 
                    className="record-button" 
                    onClick={startRecording}
                    disabled={isRecording || (responses[currentQuestion]?.answer && !isPlaying)}
                  >
                    <FaMicrophone /> {responses[currentQuestion]?.answer ? 'Re-record' : 'Record Answer'}
                  </button>
                ) : (
                  <button className="stop-button" onClick={stopRecording}>
                    <FaStop /> Stop Recording
                  </button>
                )}
                
                {responses[currentQuestion]?.answer && (
                  <div className="playback-controls">
                    {!isPlaying ? (
                      <button className="play-button" onClick={playRecording}>
                        <FaPlay /> Play
                      </button>
                    ) : (
                      <button className="pause-button" onClick={pauseRecording}>
                        <FaPause /> Pause
                      </button>
                    )}
                  </div>
                )}
                
                <audio ref={audioRef} style={{ display: 'none' }} />
              </div>
              
              {isRecording && (
                <div className="recording-indicator">
                  <div className="pulse"></div>
                  <span>Recording...</span>
                </div>
              )}
            </div>
            
            <div className="navigation-buttons">
              <button 
                className="nav-button" 
                onClick={() => currentQuestion > 0 && setCurrentQuestion(currentQuestion - 1)}
                disabled={currentQuestion === 0}
              >
                Previous
              </button>
              
              <button 
                className={`next-button ${currentQuestion === questions.length - 1 ? 'submit-button' : ''}`}
                onClick={nextQuestion}
                disabled={!responses[currentQuestion]?.answer}
              >
                {currentQuestion === questions.length - 1 ? 'Submit Interview' : 'Next Question'}
              </button>
            </div>
          </div>
          
          <div className="tips-section">
            <h3>Tips for Answering</h3>
            <ul>
              <li>Take a moment to think before you start speaking</li>
              <li>Structure your answer clearly (e.g., use the STAR method for behavioral questions)</li>
              <li>Provide specific examples from your experience</li>
              <li>Keep your answers concise but comprehensive</li>
              <li>Speak clearly and at a moderate pace</li>
            </ul>
          </div>
        </div>
      )}
      
      {stage === 'results' && score !== null && (
        <div className="results-section">
          <h1>Interview Results</h1>
          
          <div className="score-card">
            <div className="score-circle">
              <div className="score-value">{score}</div>
              <div className="score-label">Your Score</div>
            </div>
            
            <div className="score-description">
              {score >= 90 ? (
                <p>Excellent! You performed exceptionally well in this mock interview.</p>
              ) : score >= 75 ? (
                <p>Good job! You performed well, but there's still room for improvement.</p>
              ) : (
                <p>Keep practicing! Review the feedback and try again to improve your score.</p>
              )}
            </div>
          </div>
          
          <div className="feedback-section">
            <h3>Feedback & Suggestions</h3>
            <div className="feedback-content">
              <p>{feedback}</p>
              
              <h4>Areas of Strength:</h4>
              <ul>
                <li>Good communication skills</li>
                <li>Relevant experience in the field</li>
                <li>Clear articulation of thoughts</li>
              </ul>
              
              <h4>Areas for Improvement:</h4>
              <ul>
                <li>Provide more specific examples</li>
                <li>Work on structuring your answers</li>
                <li>Be more concise in your responses</li>
              </ul>
              
              <h4>Recommended Resources:</h4>
              <ul>
                <li>Book: "Cracking the Coding Interview" by Gayle Laakmann McDowell</li>
                <li>Practice common behavioral questions using the STAR method</li>
                <li>Review technical concepts related to the job description</li>
              </ul>
            </div>
          </div>
          
          <div className="action-buttons">
            <button className="retry-button" onClick={restartInterview}>
              Take Another Mock Interview
            </button>
            <button className="review-button" onClick={() => setStage('interview')}>
              Review My Answers
            </button>
          </div>
        </div>
      )}
    </div>
  );
};

export default MockInterview;

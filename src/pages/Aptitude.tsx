import React from 'react';
import { useNavigate } from 'react-router-dom';
import './Aptitude.css';

// Mock data - in a real app, this would come from an API
const aptitudeTopics = [
  { id: 'percentage', name: 'Percentage', description: 'Practice percentage calculations' },
  { id: 'profit-loss', name: 'Profit and Loss', description: 'Solve profit and loss problems' },
  { id: 'simple-interest', name: 'Simple Interest', description: 'Calculate simple interest' },
  { id: 'compound-interest', name: 'Compound Interest', description: 'Work with compound interest' },
  { id: 'time-work', name: 'Time and Work', description: 'Solve time and work problems' },
  { id: 'time-speed', name: 'Time, Speed and Distance', description: 'Calculate speed, distance, and time' },
];

const Aptitude: React.FC = () => {
  const navigate = useNavigate();

  return (
    <div className="aptitude-container">
      <h1>Aptitude Preparation</h1>
      <p className="subtitle">Select a topic to start practicing</p>
      
      <div className="topics-grid">
        {aptitudeTopics.map((topic) => (
          <div 
            key={topic.id} 
            className="topic-card"
            onClick={() => navigate(`/topic/aptitude/${topic.id}`)}
          >
            <h3>{topic.name}</h3>
            <p>{topic.description}</p>
          </div>
        ))}
      </div>
    </div>
  );
};

export default Aptitude;

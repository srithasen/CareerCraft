import React from 'react';
import { useNavigate } from 'react-router-dom';
import { FaBrain, FaCalculator, FaUserTie } from 'react-icons/fa';
import './Dashboard.css';

const Dashboard: React.FC = () => {
  const navigate = useNavigate();

  const features = [
    {
      id: 'aptitude',
      title: 'Aptitude',
      description: 'Practice aptitude questions with detailed solutions',
      icon: <FaCalculator className="feature-icon" />,
      path: '/aptitude'
    },
    {
      id: 'reasoning',
      title: 'Reasoning',
      description: 'Improve your logical and analytical reasoning skills',
      icon: <FaBrain className="feature-icon" />,
      path: '/reasoning'
    },
    {
      id: 'mock-interview',
      title: 'Mock Interview',
      description: 'Practice with AI-powered mock interviews',
      icon: <FaUserTie className="feature-icon" />,
      path: '/mock-interview'
    }
  ];

  return (
    <div className="dashboard">
      <header className="header">
        <h1>Career Path Planner</h1>
        <p>Your one-stop solution for aptitude, reasoning, and interview preparation</p>
      </header>
      
      <div className="features-grid">
        {features.map((feature) => (
          <div 
            key={feature.id} 
            className="feature-card"
            onClick={() => navigate(feature.path)}
          >
            <div className="feature-icon-container">
              {feature.icon}
            </div>
            <h3>{feature.title}</h3>
            <p>{feature.description}</p>
          </div>
        ))}
      </div>
    </div>
  );
};

export default Dashboard;

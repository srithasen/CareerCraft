import React from 'react';
import { useNavigate } from 'react-router-dom';
import './Reasoning.css';

// Mock data - in a real app, this would come from an API
const reasoningTopics = [
  { id: 'number-series', name: 'Number Series', description: 'Practice number series problems' },
  { id: 'analogies', name: 'Analogies', description: 'Solve analogy questions' },
  { id: 'blood-relations', name: 'Blood Relations', description: 'Practice blood relation problems' },
  { id: 'coding-decoding', name: 'Coding-Decoding', description: 'Solve coding-decoding questions' },
  { id: 'puzzles', name: 'Puzzles', description: 'Solve logical puzzles' },
  { id: 'syllogism', name: 'Syllogism', description: 'Practice syllogism questions' },
];

const Reasoning: React.FC = () => {
  const navigate = useNavigate();

  return (
    <div className="reasoning-container">
      <h1>Reasoning Preparation</h1>
      <p className="subtitle">Select a topic to start practicing</p>
      
      <div className="topics-grid">
        {reasoningTopics.map((topic) => (
          <div 
            key={topic.id} 
            className="topic-card"
            onClick={() => navigate(`/topic/reasoning/${topic.id}`)}
          >
            <h3>{topic.name}</h3>
            <p>{topic.description}</p>
          </div>
        ))}
      </div>
    </div>
  );
};

export default Reasoning;

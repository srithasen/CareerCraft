import React from 'react';
import { Routes, Route } from 'react-router-dom';
import Dashboard from './pages/Dashboard';
import Aptitude from './pages/Aptitude';
import Reasoning from './pages/Reasoning';
import MockInterview from './pages/MockInterview';
import TopicPage from './pages/TopicPage';
import './App.css';

const App: React.FC = () => {
  return (
    <div className="app">
      <Routes>
        <Route path="/" element={<Dashboard />} />
        <Route path="/aptitude" element={<Aptitude />} />
        <Route path="/reasoning" element={<Reasoning />} />
        <Route path="/mock-interview" element={<MockInterview />} />
        <Route path="/topic/:subject/:topicId" element={<TopicPage />} />
      </Routes>
    </div>
  );
};

export default App;

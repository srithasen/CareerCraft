import React, { createContext, useContext, useState, ReactNode } from 'react';
import { aptitudeTopics, reasoningTopics } from '../data/aptitudeData';

type Topic = {
  id: string;
  name: string;
  description: string;
  formulas: string[];
  tips: string[];
  questions: {
    id: number;
    text: string;
    options: string[];
    correctAnswer: number;
    explanation: string;
    difficulty?: 'easy' | 'medium' | 'hard';
  }[];
};

type Subject = 'aptitude' | 'reasoning';

type AppContextType = {
  currentSubject: Subject | null;
  currentTopic: Topic | null;
  currentQuestionIndex: number;
  selectedOption: number | null;
  showSolution: boolean;
  score: number;
  setCurrentSubject: (subject: Subject | null) => void;
  setCurrentTopic: (topic: Topic | null) => void;
  setCurrentQuestionIndex: (index: number) => void;
  setSelectedOption: (option: number | null) => void;
  setShowSolution: (show: boolean) => void;
  setScore: (score: number) => void;
  getTopics: (subject: Subject) => Topic[];
  getTopicById: (subject: Subject, topicId: string) => Topic | undefined;
  generateMoreQuestions: (topic: Topic, count: number) => void;
};

const AppContext = createContext<AppContextType | undefined>(undefined);

export const AppProvider: React.FC<{ children: ReactNode }> = ({ children }) => {
  const [currentSubject, setCurrentSubject] = useState<Subject | null>(null);
  const [currentTopic, setCurrentTopic] = useState<Topic | null>(null);
  const [currentQuestionIndex, setCurrentQuestionIndex] = useState(0);
  const [selectedOption, setSelectedOption] = useState<number | null>(null);
  const [showSolution, setShowSolution] = useState(false);
  const [score, setScore] = useState(0);
  const [topics, setTopics] = useState({
    aptitude: [...aptitudeTopics],
    reasoning: [...reasoningTopics],
  });

  const getTopics = (subject: Subject): Topic[] => {
    return topics[subject];
  };

  const getTopicById = (subject: Subject, topicId: string): Topic | undefined => {
    return topics[subject].find((topic) => topic.id === topicId);
  };

  const generateMoreQuestions = (topic: Topic, count: number) => {
    // In a real app, this would fetch new questions from an API
    // For now, we'll just duplicate existing questions with new IDs
    const newQuestions = [];
    const existingQuestions = topic.questions;
    
    for (let i = 0; i < count; i++) {
      const question = {
        ...existingQuestions[i % existingQuestions.length],
        id: existingQuestions.length + i + 1,
      };
      newQuestions.push(question);
    }

    setTopics((prev) => ({
      ...prev,
      [currentSubject as Subject]: prev[currentSubject as Subject].map((t) =>
        t.id === topic.id
          ? { ...t, questions: [...t.questions, ...newQuestions] }
          : t
      ),
    }));

    if (currentTopic) {
      setCurrentTopic({
        ...currentTopic,
        questions: [...currentTopic.questions, ...newQuestions],
      });
    }
  };

  return (
    <AppContext.Provider
      value={{
        currentSubject,
        currentTopic,
        currentQuestionIndex,
        selectedOption,
        showSolution,
        score,
        setCurrentSubject,
        setCurrentTopic,
        setCurrentQuestionIndex,
        setSelectedOption,
        setShowSolution,
        setScore,
        getTopics,
        getTopicById,
        generateMoreQuestions,
      }}
    >
      {children}
    </AppContext.Provider>
  );
};

export const useAppContext = (): AppContextType => {
  const context = useContext(AppContext);
  if (!context) {
    throw new Error('useAppContext must be used within an AppProvider');
  }
  return context;
};

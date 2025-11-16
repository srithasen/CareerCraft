interface Question {
  id: number;
  text: string;
  options: string[];
  correctAnswer: number;
  explanation: string;
  difficulty?: 'easy' | 'medium' | 'hard';
}

interface Topic {
  id: string;
  name: string;
  description: string;
  formulas: string[];
  tips: string[];
  questions: Question[];
}

export const aptitudeTopics: Topic[] = [
  {
    id: 'average',
    name: 'Average',
    description: 'Practice problems related to calculating averages of numbers and their applications',
    formulas: [
      'Average = Sum of Quantities / Number of Quantities',
      'If average of n numbers is A and each number is increased by x, then new average = A + x',
      'If average of n numbers is A and each number is multiplied by x, then new average = A * x',
      'Average of first n natural numbers = (n + 1) / 2',
      'Average of first n even numbers = n + 1',
      'Average of first n odd numbers = n'
    ],
    tips: [
      'For consecutive numbers, the average of first and last term is the average of all terms',
      'If the average of a group changes after adding/removing an element, use the concept of total sum',
      'For problems involving replacement, calculate the difference in total sum',
      'In age problems, always consider the change in total age'
    ],
    questions: [
      {
        id: 1,
        text: 'The average of 5 consecutive even numbers is 20. What is the largest of these numbers?',
        options: ['20', '22', '24', '26'],
        correctAnswer: 2,
        explanation: 'Let the numbers be x, x+2, x+4, x+6, x+8. Average = (5x + 20)/5 = x + 4 = 20. So, x = 16. Largest number = x + 8 = 24',
        difficulty: 'easy'
      },
      {
        id: 2,
        text: 'The average of 11 numbers is 30. If the average of first six numbers is 17.5 and that of last six is 42.5, what is the sixth number?',
        options: ['30', '36', '45', '47'],
        correctAnswer: 0,
        explanation: 'Sum of first six numbers = 6 × 17.5 = 105. Sum of last six numbers = 6 × 42.5 = 255. Sum of all 11 numbers = 11 × 30 = 330. Sixth number = (105 + 255) - 330 = 30',
        difficulty: 'medium'
      },
      {
        id: 3,
        text: 'The average age of a class of 29 students is 20 years. If the age of teacher is included, the average increases by 3 months. Find the age of the teacher.',
        options: ['25.2 years', '27.5 years', '29 years', '31.5 years'],
        correctAnswer: 1,
        explanation: 'Total age of 29 students = 29 × 20 = 580 years. New average = 20.25 years. Total age of 30 people = 30 × 20.25 = 607.5 years. Teacher\'s age = 607.5 - 580 = 27.5 years',
        difficulty: 'hard'
      }
    ]
  },
  {
    id: 'time-distance',
    name: 'Time & Distance',
    description: 'Solve problems related to time, speed and distance calculations',
    formulas: [
      'Distance = Speed × Time',
      'Speed = Distance / Time',
      'Time = Distance / Speed',
      '1 km/hr = (5/18) m/s',
      '1 m/s = (18/5) km/hr',
      'If a person covers same distance with speeds v₁ and v₂, then average speed = 2v₁v₂/(v₁ + v₂)'
    ],
    tips: [
      'Convert all units to same system (km/h to m/s or vice versa) before solving',
      'For relative speed problems, add speeds when moving in opposite directions and subtract when moving in same direction',
      'For train problems, consider the length of the train when passing a pole or platform',
      'In boat and stream problems, downstream speed = boat speed + stream speed, upstream speed = boat speed - stream speed'
    ],
    questions: [
      {
        id: 1,
        text: 'A train 200 m long passes a pole in 15 seconds. What is the speed of the train in km/h?',
        options: ['36 km/h', '48 km/h', '54 km/h', '60 km/h'],
        correctAnswer: 1,
        explanation: 'Speed = Distance/Time = 200m/15s = (200/1000)km / (15/3600)h = (0.2 × 3600)/15 = 48 km/h',
        difficulty: 'easy'
      },
      {
        id: 2,
        text: 'A person covers a distance of 60 km from P to Q at 20 km/h and returns at 30 km/h. What is the average speed for the whole journey?',
        options: ['22 km/h', '24 km/h', '25 km/h', '28 km/h'],
        correctAnswer: 1,
        explanation: 'Total distance = 60 + 60 = 120 km. Time taken = (60/20) + (60/30) = 3 + 2 = 5 hours. Average speed = 120/5 = 24 km/h',
        difficulty: 'medium'
      }
    ]
  },
  {
    id: 'time-work',
    name: 'Time & Work',
    description: 'Solve problems related to work efficiency and time management',
    formulas: [
      'Work = Time × Efficiency',
      'If A can do a work in x days, then work done by A in 1 day = 1/x',
      'If A is n times as efficient as B, then time taken by A = (1/n) × time taken by B',
      'If A and B can do a work in x and y days respectively, then together they can complete the work in (xy)/(x + y) days',
      'If A, B, and C can do a work in x, y, and z days respectively, then together they can complete the work in (xyz)/(xy + yz + zx) days'
    ],
    tips: [
      'Assume total work to be LCM of individual time taken for easier calculations',
      'For wages problems, wages are distributed in the ratio of work done',
      'If A is twice as efficient as B, then A will take half the time taken by B',
      'For pipe and cistern problems, inlet pipes fill while outlet pipes empty the tank'
    ],
    questions: [
      {
        id: 1,
        text: 'A can do a work in 15 days and B in 20 days. If they work together for 4 days, what fraction of work is left?',
        options: ['1/4', '2/5', '8/15', '7/15'],
        correctAnswer: 2,
        explanation: 'A\'s 1 day work = 1/15, B\'s 1 day work = 1/20. Combined 1 day work = 1/15 + 1/20 = 7/60. In 4 days, work done = 4 × 7/60 = 7/15. Work left = 1 - 7/15 = 8/15',
        difficulty: 'easy'
      }
    ]
  },
  {
    id: 'hcf-lcm',
    name: 'HCF & LCM',
    description: 'Problems on highest common factor and least common multiple',
    formulas: [
      'Product of two numbers = HCF × LCM',
      'HCF of fractions = HCF of numerators / LCM of denominators',
      'LCM of fractions = LCM of numerators / HCF of denominators',
      'For two numbers, if one is a multiple of the other, then the smaller number is the HCF and larger number is the LCM'
    ],
    tips: [
      'For finding HCF, use prime factorization or division method',
      'LCM is always greater than or equal to the largest number',
      'HCF is always less than or equal to the smallest number',
      'For more than two numbers, find HCF/LCM of first two numbers, then with the result and next number'
    ],
    questions: [
      {
        id: 1,
        text: 'The HCF of two numbers is 11 and their LCM is 693. If one number is 77, find the other number.',
        options: ['99', '121', '143', '187'],
        correctAnswer: 0,
        explanation: 'Using the formula: Product of numbers = HCF × LCM. Let the numbers be 77 and x. So, 77 × x = 11 × 693. Solving, x = (11 × 693)/77 = 99',
        difficulty: 'medium'
      }
    ]
  },
  {
    id: 'profit-loss',
    name: 'Profit & Loss',
    description: 'Calculate profit, loss, discount, and marked price',
    formulas: [
      'Profit = Selling Price (SP) - Cost Price (CP)',
      'Loss = CP - SP',
      'Profit % = (Profit/CP) × 100',
      'Loss % = (Loss/CP) × 100',
      'Selling Price = CP × (1 + Profit%/100) or CP × (1 - Loss%/100)',
      'Discount = Marked Price - Selling Price',
      'Discount % = (Discount/Marked Price) × 100'
    ],
    tips: [
      'CP is always 100% for profit/loss calculations',
      'For successive discounts, use the formula: Total discount = a + b - (a×b)/100',
      'When profit and loss percentage are equal, there is always a loss of (x²/100)% where x is the profit/loss percentage',
      'For false weights, use the formula: Gain% = [(True Weight - False Weight)/False Weight] × 100'
    ],
    questions: [
      {
        id: 1,
        text: 'A shopkeeper sells an article at a profit of 20%. If he had bought it at 20% less and sold it for Rs. 20 less, he would have gained 25%. Find the cost price of the article.',
        options: ['Rs. 500', 'Rs. 600', 'Rs. 700', 'Rs. 800'],
        correctAnswer: 0,
        explanation: 'Let CP = 100x. Then SP = 120x. New CP = 80x. New SP = 125% of 80x = 100x. According to question, 120x - 100x = 20 ⇒ 20x = 20 ⇒ x = 1. So, CP = 100 × 5 = Rs. 500',
        difficulty: 'hard'
      }
    ]
  },
  {
    id: 'percentage',
    name: 'Percentage',
    description: 'Problems involving percentages and their applications',
    formulas: [
      'x% of y = (x/100) × y',
      'x is what % of y = (x/y) × 100',
      'Percentage increase/decrease = (Change/Original) × 100',
      'If a number changes from x to y, then % change = ((y - x)/x) × 100',
      'If the price of an item increases by x%, then the reduction in consumption to keep expenditure same is (100x)/(100 + x)%',
      'If the price of an item decreases by x%, then the increase in consumption to keep expenditure same is (100x)/(100 - x)%'
    ],
    tips: [
      'For percentage increase/decrease, always take the original value as 100',
      'To find original value after percentage change, use: Original = (Final × 100)/(100 ± change%)',
      'For successive percentage changes, multiply the factors (1 ± x/100)',
      'To compare percentage changes, always consider the base value'
    ],
    questions: [
      {
        id: 1,
        text: 'If the price of sugar is increased by 25%, by how much percent should a householder reduce his consumption so that there is no increase in his expenditure on sugar?',
        options: ['20%', '25%', '30%', '35%'],
        correctAnswer: 0,
        explanation: 'Using formula: Reduction in consumption = (100 × 25)/(100 + 25) = 2500/125 = 20%',
        difficulty: 'medium'
      },
      {
        id: 2,
        text: 'A number is first increased by 20% and then decreased by 20%. What is the net percentage change?',
        options: ['4% increase', '4% decrease', 'No change', 'Cannot be determined'],
        correctAnswer: 1,
        explanation: 'Net effect = 20 - 20 + (20 × -20)/100 = -4%. So, 4% decrease',
        difficulty: 'easy'
      }
    ]
  },
  {
    id: 'simple-compound-interest',
    name: 'Simple & Compound Interest',
    description: 'Calculate simple and compound interest on principal amounts',
    formulas: [
      'Simple Interest (SI) = (P × R × T)/100',
      'Amount (A) = P + SI = P(1 + RT/100)',
      'Compound Interest (CI) = P(1 + R/100)ⁿ - P',
      'Amount (A) = P(1 + R/100)ⁿ',
      'For half-yearly compounding: A = P(1 + (R/2)/100)²ⁿ',
      'For quarterly compounding: A = P(1 + (R/4)/100)⁴ⁿ',
      'Difference between CI and SI for 2 years = PR²/10000',
      'Difference between CI and SI for 3 years = PR²(300 + R)/1000000'
    ],
    tips: [
      'For simple interest, the principal remains constant throughout the time period',
      'For compound interest, the amount at the end of each year becomes the principal for the next year',
      'When interest is compounded half-yearly, time is doubled and rate is halved',
      'When interest is compounded quarterly, time is multiplied by 4 and rate is divided by 4'
    ],
    questions: [
      {
        id: 1,
        text: 'A sum of money doubles itself in 5 years at simple interest. In how many years will it become 6 times itself?',
        options: ['15 years', '20 years', '25 years', '30 years'],
        correctAnswer: 2,
        explanation: 'If sum doubles in 5 years, then rate = 100/5 = 20%. To become 6 times, interest should be 5P. So, 5P = (P × 20 × T)/100 ⇒ T = 25 years',
        difficulty: 'medium'
      },
      {
        id: 2,
        text: 'The difference between compound interest and simple interest on a sum of Rs. 10,000 for 2 years at 10% per annum is:',
        options: ['Rs. 10', 'Rs. 100', 'Rs. 1000', 'Rs. 10000'],
        correctAnswer: 1,
        explanation: 'Difference = PR²/10000 = (10000 × 10 × 10)/10000 = Rs. 100',
        difficulty: 'easy'
      }
    ]
  },
  {
    id: 'ratio-proportion',
    name: 'Ratio & Proportion',
    description: 'Solve problems involving ratios, proportions, and variations',
    formulas: [
      'If a:b = c:d, then a/b = c/d',
      'If a/b = c/d, then a×d = b×c (Product of means = Product of extremes)',
      'If a:b and b:c are given, then a:b:c can be found by making b equal in both ratios',
      'If a/b = c/d, then (a + b)/(a - b) = (c + d)/(c - d) [Componendo and Dividendo]',
      'If a/b = c/d = e/f = k, then (a + c + e)/(b + d + f) = k',
      'If a/b = c/d, then (a + c)/(b + d) = a/b = c/d'
    ],
    tips: [
      'For ratio problems, assume a common multiple of the given ratios',
      'When quantities are in ratio a:b, they can be represented as ak and bk',
      'For problems involving mixtures, use the rule of alligation',
      'For partnership problems, profit is divided in the ratio of investments multiplied by time'
    ],
    questions: [
      {
        id: 1,
        text: 'The ratio of boys to girls in a class is 3:2. If 5 more boys and 10 more girls join the class, the ratio becomes 5:4. Find the initial number of students in the class.',
        options: ['30', '40', '50', '60'],
        correctAnswer: 2,
        explanation: 'Let initial number of boys = 3x, girls = 2x. Then (3x + 5)/(2x + 10) = 5/4. Solving, x = 10. Total students = 3x + 2x = 5x = 50',
        difficulty: 'medium'
      }
    ]
  },
  {
    id: 'permutations-combinations',
    name: 'Permutations & Combinations',
    description: 'Solve problems involving arrangements and selections',
    formulas: [
      'n! = n × (n-1) × (n-2) × ... × 1',
      'Permutation (Arrangement): nPr = n!/(n-r)!',
      'Combination (Selection): nCr = n!/(r!(n-r)!)',
      'Number of ways to arrange n items = n!',
      'Number of ways to arrange n items where p are identical = n!/p!',
      'Number of diagonals in a polygon with n sides = nC₂ - n',
      'Number of handshakes among n people = nC₂'
    ],
    tips: [
      'Use permutation when order matters, combination when order does not matter',
      'For circular arrangements, number of ways = (n-1)!',
      'For arrangements with restrictions, first arrange the restricted items',
      'For at least/at most problems, consider complementary counting'
    ],
    questions: [
      {
        id: 1,
        text: 'In how many ways can the letters of the word "LEADING" be arranged so that the vowels always come together?',
        options: ['360', '720', '1440', '5040'],
        correctAnswer: 1,
        explanation: 'Treat EAI as one unit. Total units = L, D, N, G, (EAI) = 5 units. These can be arranged in 5! = 120 ways. EAI can be arranged in 3! = 6 ways. Total arrangements = 120 × 6 = 720',
        difficulty: 'medium'
      }
    ]
  },
  {
    id: 'probability',
    name: 'Probability',
    description: 'Calculate probabilities of events',
    formulas: [
      'Probability of an event E, P(E) = Number of favorable outcomes / Total number of possible outcomes',
      'P(not E) = 1 - P(E)',
      'For independent events A and B: P(A and B) = P(A) × P(B)',
      'For mutually exclusive events A and B: P(A or B) = P(A) + P(B)',
      'For any two events A and B: P(A or B) = P(A) + P(B) - P(A and B)',
      'Conditional probability: P(A|B) = P(A and B)/P(B)'
    ],
    tips: [
      'Probability always lies between 0 and 1 (inclusive)',
      'For 'at least one' problems, use P(at least one) = 1 - P(none)',
      'For 'either-or' problems, add the probabilities',
      'For 'and' problems, multiply the probabilities'
    ],
    questions: [
      {
        id: 1,
        text: 'Two dice are thrown simultaneously. What is the probability of getting a sum of 7?',
        options: ['1/6', '1/12', '1/36', '5/36'],
        correctAnswer: 0,
        explanation: 'Favorable outcomes: (1,6), (2,5), (3,4), (4,3), (5,2), (6,1) = 6. Total outcomes = 6 × 6 = 36. Probability = 6/36 = 1/6',
        difficulty: 'easy'
      }
    ]
  }
];

export const reasoningTopics = [
  {
    id: 'blood-relations',
    name: 'Blood Relations',
    description: 'Solve problems involving family relationships',
    formulas: [],
    tips: [
      'Draw a family tree for complex relationships',
      'Use + for male and - for female in your diagram',
      'Identify the relationship by moving step by step from the given information',
      'Remember standard relationships: father\'s father is grandfather, mother\'s brother is maternal uncle, etc.'
    ],
    questions: [
      {
        id: 1,
        text: 'Pointing to a woman, Naman said, "She is the daughter of the only child of my grandmother." How is the woman related to Naman?',
        options: ['Sister', 'Aunt', 'Mother', 'Daughter'],
        correctAnswer: 0,
        explanation: 'Only child of Naman\'s grandmother is Naman\'s parent. Daughter of Naman\'s parent is Naman\'s sister.',
        difficulty: 'easy'
      }
    ]
  },
  {
    id: 'syllogisms',
    name: 'Syllogisms',
    description: 'Solve logical reasoning problems with statements and conclusions',
    formulas: [],
    tips: [
      'Draw Venn diagrams to visualize the relationships',
      'Check if the conclusion follows definitely or only possibly',
      'Look for key terms like some, all, no, only, etc.',
      'Remember that 'some A are B' does not mean 'some A are not B''
    ],
    questions: [
      {
        id: 1,
        text: 'Statements: All roses are flowers. Some flowers fade quickly. Conclusions: I. Some roses fade quickly. II. All flowers fade quickly.',
        options: ['Only I follows', 'Only II follows', 'Both I and II follow', 'Neither I nor II follows'],
        correctAnswer: 3,
        explanation: 'From the given statements, we cannot definitely conclude that some roses fade quickly or that all flowers fade quickly. The word "some" in the second statement means at least one, but not necessarily all.',
        difficulty: 'medium'
      }
    ]
  },
  {
    id: 'puzzles',
    name: 'Puzzles',
    description: 'Solve logical puzzles and seating arrangements',
    formulas: [],
    tips: [
      'Create a table or grid to organize the information',
      'Look for definite information first',
      'Use the process of elimination',
      'Make logical deductions from each clue'
    ],
    questions: [
      {
        id: 1,
        text: 'Five friends A, B, C, D, and E are sitting in a row. A is to the right of B, E is to the left of C but to the right of A, and D is to the right of E. Who is in the middle?',
        options: ['A', 'B', 'C', 'E'],
        correctAnswer: 3,
        explanation: 'From the clues: B is to the left of A, A is to the left of E, E is to the left of C, and E is to the left of D. So the order is B, A, E, C/D, D/C. E is in the middle.',
        difficulty: 'medium'
      }
    ]
  },
  {
    id: 'coding-decoding',
    name: 'Coding-Decoding',
    description: 'Solve problems involving letter and number codes',
    formulas: [],
    tips: [
      'Look for patterns in letter positions (A=1, B=2, etc.)',
      'Check for reverse alphabetical positions (Z=1, Y=2, etc.)',
      'Look for letter shifts (A→C, B→D, etc.)',
      'Check for number patterns or mathematical operations on letter positions'
    ],
    questions: [
      {
        id: 1,
        text: 'In a certain code, 'DELHI' is written as 'CDKGH'. How is 'MUMBAI' written in that code?',
        options: ['LTLABH', 'LTLAAH', 'LTLBZH', 'LTLAZH'],
        correctAnswer: 1,
        explanation: 'Each letter is moved one position backward in the alphabet: D→C, E→D, L→K, H→G, I→H. Applying same to MUMBAI: M→L, U→T, M→L, B→A, A→Z, I→H. But since A is the first letter, it wraps around to Z. So A→Z, and I→H.',
        difficulty: 'hard'
      }
    ]
  },
  {
    id: 'direction-sense',
    name: 'Direction Sense',
    description: 'Solve problems involving directions and distances',
    formulas: [],
    tips: [
      'Draw the path step by step',
      'Mark the starting point and track each movement',
      'Use the concept of right angles (90°) for turns',
      'Remember the cardinal directions: N, E, S, W in clockwise order'
    ],
    questions: [
      {
        id: 1,
        text: 'A person walks 5 km towards East, then turns right and walks 3 km, then turns right again and walks 5 km. In which direction is he from the starting point?',
        options: ['North', 'South', 'East', 'South-East'],
        correctAnswer: 3,
        explanation: 'Starting from origin (0,0), after moving 5 km East: (5,0). Then 3 km South: (5,-3). Then 5 km West: (0,-3). The final position is 3 km South of the starting point, so South-East direction from start.',
        difficulty: 'medium'
      }
    ]
  }
];

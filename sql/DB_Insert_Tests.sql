-- Insert Tests

INSERT INTO Business (Name, Password, Email) VALUES
('Amazon', 'bezos', 'prime@amazon.com'),
('Facebook', 'zucc', 'facebook@meta.com');


INSERT INTO Investor (Name, Password, Email, InvestorBalance) VALUES
('John Smith', 'test', 'john.smith@investor.com', 5000.00),
('Jane Doe', 'test', 'jane.doe@investor.com', 7500.50);

INSERT INTO Investor (Name, Password, Email, InvestorBalance) VALUES
('Mr Test', '1234', 'test@investor.com', 10000.00);


INSERT INTO Pitch (Title, ElevatorPitch, DetailedPitch, TargetAmount, CurrentAmount, WindowEndDate, PayoutFrequency, ProfitSharePercentage, BusinessID) VALUES
('Food Delivery Drone', 'An autonomous drone service for fast, contactless food delivery.', 'Our innovative drone network uses AI to optimize delivery routes, ensuring meals arrive hot and on time. We are targeting high-density urban areas for our initial rollout.', 50000.00, 10000.00, '2026-10-01', 'Quarterly', 25.00, 1),
('Augmented Reality Glasses', 'Stylish AR glasses that seamlessly overlay digital information onto the real world.', 'These lightweight glasses provide a transparent display for navigation, notifications, and interactive experiences without obstructing your view. They are designed for both professional and casual use.', 15000.00, 0.00, '2026-11-15', 'Annually', 20.00, 2);


INSERT INTO InvestmentTier (Name, Min, Max, SharePercentage, Multiplier, PitchID) VALUES
('Bronze Tier', 10.00, 99.99, 1.00, 1.05, 1),
('Silver Tier', 100.00, 499.99, 2.50, 1.10, 1),
('Basic Tier', 25.00, 250.00, 1.00, 1.08, 2),
('Premium Tier', 251.00, 15000.00, 3.00, 1.12, 2);


INSERT INTO Investment (Amount, DateMade, PitchID, InvestorID, ROI, CalculateShare) VALUES
(100.00, '2025-09-20', 1, 1, 0.00, (SELECT SharePercentage FROM InvestmentTier WHERE PitchID = 1 AND 100.00 BETWEEN Min AND Max) * (SELECT Multiplier FROM InvestmentTier WHERE PitchID = 1 AND 100.00 BETWEEN Min AND Max)),
(50.00, '2025-09-21', 2, 2, 0.00, (SELECT SharePercentage FROM InvestmentTier WHERE PitchID = 2 AND 50.00 BETWEEN Min AND Max) * (SELECT Multiplier FROM InvestmentTier WHERE PitchID = 2 AND 50.00 BETWEEN Min AND Max));


INSERT INTO Bank (AccountNumber, HolderName, Balance) VALUES
('1234567890', 'J Smith', 100000.00),
('0987654321', 'J Doe', 50000.00);


INSERT INTO Media (FilePath, PitchID) VALUES
('/path/to/media/drone_video.mp4', 1),
('/path/to/media/glasses_photo.jpg', 2);


INSERT INTO ProfitDistribution (PitchID, Profit, DistributionDate) VALUES
(1, 5000.00, '2025-10-15'),
(2, 3000.00, '2025-10-15');

--example tags
INSERT INTO Tag (Name) VALUES
('Eco-Friendly'),
('Low Risk'),
('High Risk'),
('Technology'),
('Healthcare'),
('Food & Beverage'),
('Education'),
('Sustainable'),
('FinTech'),
('Real Estate'),
('Consumer Goods'),
('Renewable Energy'),
('Social Impact'),
('Startup');
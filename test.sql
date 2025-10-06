-- =====================================
-- RESET DATABASE
-- =====================================

-- Drop child tables first
DROP TABLE IF EXISTS PitchTag;
DROP TABLE IF EXISTS Investment;
DROP TABLE IF EXISTS InvestmentTier;
DROP TABLE IF EXISTS Media;
DROP TABLE IF EXISTS ProfitDistribution;

-- Drop parent tables
DROP TABLE IF EXISTS Pitch;
DROP TABLE IF EXISTS Investor;
DROP TABLE IF EXISTS Business;
DROP TABLE IF EXISTS Bank;
DROP TABLE IF EXISTS Tag;

-- =====================================
-- CREATE TABLES
-- =====================================

-- Business users
CREATE TABLE Business (
    BusinessID INT PRIMARY KEY AUTO_INCREMENT,
    Name VARCHAR(255) NOT NULL,
    Password VARCHAR(255) NOT NULL,
    Email VARCHAR(255) NOT NULL UNIQUE
);

-- Investor users
CREATE TABLE Investor (
    InvestorID INT PRIMARY KEY AUTO_INCREMENT,
    Name VARCHAR(255) NOT NULL,
    Password VARCHAR(255) NOT NULL,
    Email VARCHAR(255) NOT NULL UNIQUE,
    InvestorBalance DECIMAL(10,2) DEFAULT 0.00
    Address VARCHAR(255),
    DateOfBirth DATE,
    Nationality VARCHAR(100),
    PreferredCurrency VARCHAR(50);
);

-- Pitches
CREATE TABLE Pitch (
    PitchID INT PRIMARY KEY AUTO_INCREMENT,
    Title VARCHAR(255) NOT NULL,
    ElevatorPitch TEXT,
    DetailedPitch TEXT,
    TargetAmount DECIMAL(10, 2) NOT NULL,
    CurrentAmount DECIMAL(10, 2) DEFAULT 0.00,
    WindowEndDate DATE,
    PayoutFrequency VARCHAR(50),
    ProfitSharePercentage DECIMAL(5, 2) NOT NULL,
    Status ENUM('draft', 'active', 'funded', 'closed') NOT NULL DEFAULT 'draft',
    BusinessID INT,
    FOREIGN KEY (BusinessID) REFERENCES Business(BusinessID)
);

-- Media table
CREATE TABLE Media (
    MediaID INT PRIMARY KEY AUTO_INCREMENT,
    FilePath VARCHAR(255) NOT NULL,
    PitchID INT,
    FOREIGN KEY (PitchID) REFERENCES Pitch(PitchID)
);

-- Investment tiers
CREATE TABLE InvestmentTier (
    InvestmentTierID INT PRIMARY KEY AUTO_INCREMENT,
    Name VARCHAR(255) NOT NULL,
    Min DECIMAL(10, 2) NOT NULL,
    Max DECIMAL(10, 2) NOT NULL,
    SharePercentage DECIMAL(5,2) NOT NULL,
    Multiplier DECIMAL(5, 2) NOT NULL,
    PitchID INT,
    FOREIGN KEY (PitchID) REFERENCES Pitch(PitchID)
);

-- Investments
CREATE TABLE Investment (
    InvestmentID INT PRIMARY KEY AUTO_INCREMENT,
    Amount DECIMAL(10, 2) NOT NULL,
    DateMade DATE,
    PitchID INT,
    InvestorID INT,
    ROI DECIMAL(10, 2) DEFAULT 0.00,
    CalculateShare DECIMAL(10, 2),
    FOREIGN KEY (PitchID) REFERENCES Pitch(PitchID),
    FOREIGN KEY (InvestorID) REFERENCES Investor(InvestorID)
);

-- Profit distribution
CREATE TABLE ProfitDistribution (
    DistributionID INT PRIMARY KEY AUTO_INCREMENT,
    PitchID INT NOT NULL,
    Profit DECIMAL(10, 2) NOT NULL,
    DistributionDate DATE NOT NULL,
    FOREIGN KEY (PitchID) REFERENCES Pitch(PitchID)
);

-- Bank
CREATE TABLE Bank (
    AccountID INT PRIMARY KEY AUTO_INCREMENT,
    AccountNumber VARCHAR(255) NOT NULL,
    HolderName VARCHAR(255) NOT NULL,
    Balance DECIMAL(10,2) default 0.00
);

-- Tags
CREATE TABLE Tag (
    TagID INT PRIMARY KEY AUTO_INCREMENT,
    Name VARCHAR(50) NOT NULL UNIQUE
);

-- PitchTag linking
CREATE TABLE PitchTag (
    PitchID INT,
    TagID INT,
    PRIMARY KEY (PitchID, TagID),
    FOREIGN KEY (PitchID) REFERENCES Pitch(PitchID) ON DELETE CASCADE,
    FOREIGN KEY (TagID) REFERENCES Tag(TagID) ON DELETE CASCADE
);

CREATE INDEX idx_tagid ON PitchTag(TagID);

-- =====================================
-- INSERT MOCK DATA
-- =====================================

-- Businesses
INSERT INTO Business (Name, Password, Email) VALUES
('Acme Corp', 'pass123', 'acme@example.com'),
('Globex Ltd', 'pass123', 'globex@example.com'),
('Innotech', 'pass123', 'innotech@example.com'),
('BlueWave', 'pass123', 'bluewave@example.com'),
('NextGen Solutions', 'pass123', 'nextgen@example.com');

-- Investors
INSERT INTO Investor (Name, Password, Email, InvestorBalance) VALUES
('Alice Investor', 'pass', 'alice@example.com', 5000),
('Bob Investor', 'pass', 'bob@example.com', 10000),
('Charlie Investor', 'pass', 'charlie@example.com', 7500),
('Diana Investor', 'pass', 'diana@example.com', 3000),
('Eve Investor', 'pass', 'eve@example.com', 12000);

-- Tags
INSERT INTO Tag (Name) VALUES
('Tech'), ('Health'), ('Education'), ('Energy'), ('Food'), ('AI'), ('SaaS'), ('GreenTech'), ('FinTech'), ('Entertainment');

-- Pitches
INSERT INTO Pitch (Title, ElevatorPitch, DetailedPitch, TargetAmount, CurrentAmount, WindowEndDate, PayoutFrequency, ProfitSharePercentage, Status, BusinessID) VALUES
('Solar Energy Startup', 'Clean energy solution.', 'We provide solar panels for homes and businesses with innovative storage solutions.', 10000, 2000, '2025-12-31', 'Monthly', 15, 'active', 1),
('EdTech Platform', 'Revolutionizing online learning.', 'Our platform provides AI-driven lessons for students worldwide.', 15000, 5000, '2025-11-30', 'Quarterly', 10, 'active', 2),
('AI Health Assistant', 'Personal health assistant.', 'AI-powered platform to track and improve your health metrics.', 20000, 12000, '2025-12-15', 'Monthly', 12, 'active', 3),
('Food Delivery App', 'Fast & fresh meals.', 'We deliver freshly prepared meals within 30 minutes using local chefs.', 8000, 4000, '2025-10-31', 'Quarterly', 8, 'active', 4),
('GreenTech Vehicles', 'Eco-friendly transport.', 'Manufacturing electric bikes and scooters for urban commuting.', 25000, 10000, '2026-01-31', 'Monthly', 18, 'active', 5);

-- Investment Tiers
INSERT INTO InvestmentTier (Name, Min, Max, SharePercentage, Multiplier, PitchID) VALUES
('Bronze', 100, 999, 5, 1.0, 1),
('Silver', 1000, 4999, 7, 1.2, 1),
('Gold', 5000, 9999, 10, 1.5, 1),
('Starter', 50, 499, 3, 1.0, 2),
('Pro', 500, 4999, 6, 1.2, 2),
('Elite', 5000, 10000, 12, 1.5, 2),
('Basic', 100, 999, 4, 1.0, 3),
('Advanced', 1000, 4999, 8, 1.2, 3),
('Premium', 5000, 20000, 15, 1.5, 3),
('Small', 50, 299, 2, 1.0, 4),
('Medium', 300, 1999, 5, 1.2, 4),
('Large', 2000, 8000, 9, 1.5, 4),
('EcoBasic', 100, 999, 6, 1.0, 5),
('EcoPro', 1000, 4999, 12, 1.3, 5),
('EcoElite', 5000, 25000, 18, 1.5, 5);

-- Pitch tags
INSERT INTO PitchTag (PitchID, TagID) VALUES
(1, 1), (1, 4), (2, 3), (2, 6), (3, 2), (3, 6), (4, 5), (4, 9), (5, 1), (5, 8);


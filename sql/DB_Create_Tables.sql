-- Create Tables

-- CREATE DATABASE AC41004_DB;

-- Testing
DROP TABLE IF EXISTS PitchTag;
DROP TABLE IF EXISTS Tag;
DROP TABLE IF EXISTS Investment;
DROP TABLE IF EXISTS InvestmentTier;
DROP TABLE IF EXISTS Media;
DROP TABLE IF EXISTS ProfitDistribution;
DROP TABLE IF EXISTS Pitch;
DROP TABLE IF EXISTS Investor;
DROP TABLE IF EXISTS Business;
DROP TABLE IF EXISTS Bank;


-- Create Tables

-- Business user type
CREATE TABLE Business (
    BusinessID INT PRIMARY KEY AUTO_INCREMENT,
    Name VARCHAR(255) NOT NULL,
    Password VARCHAR(255) NOT NULL,
    Email VARCHAR(255) NOT NULL UNIQUE
);

-- Investor user type
CREATE TABLE Investor (
    InvestorID INT PRIMARY KEY AUTO_INCREMENT,
    Name VARCHAR(255) NOT NULL,
    Password VARCHAR(255) NOT NULL,
    Email VARCHAR(255) NOT NULL UNIQUE,
    InvestorBalance DECIMAL(10,2) DEFAULT 0.00
);

-- Pitch that bussinesses can create (NEED TO HAVE SAVED MEDIA IMAGES/VIDEOS)
CREATE TABLE Pitch (
    PitchID INT PRIMARY KEY AUTO_INCREMENT,
    Title VARCHAR(255) NOT NULL,
    ElevatorPitch TEXT,
    DetailedPitch TEXT,
    TargetAmount DECIMAL(10, 2) NOT NULL,
    CurrentAmount DECIMAL(10, 2) DEFAULT 0.00,
    WindowEndDate DATE,
    PayoutFrequency VARCHAR(50), -- Quarterly or Annually etc.
	ProfitSharePercentage DECIMAL(5, 2) NOT NULL,
    BusinessID INT,
    FOREIGN KEY (BusinessID) REFERENCES Business(BusinessID)
);

-- Media table to store file paths
CREATE TABLE Media (
    MediaID INT PRIMARY KEY AUTO_INCREMENT,
    FilePath VARCHAR(255) NOT NULL,
    PitchID INT,
    FOREIGN KEY (PitchID) REFERENCES Pitch(PitchID)
);

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

-- Investments created by investors
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

-- Track pitch profit for payout
CREATE TABLE ProfitDistribution (
    DistributionID INT PRIMARY KEY AUTO_INCREMENT,
    PitchID INT NOT NULL,
    Profit DECIMAL(10, 2) NOT NULL,
    DistributionDate DATE NOT NULL,
    FOREIGN KEY (PitchID) REFERENCES Pitch(PitchID)
);

-- Mock bank for payments into account or pitch
CREATE TABLE Bank (
    AccountID INT PRIMARY KEY AUTO_INCREMENT,
    AccountNumber VARCHAR(255) NOT NULL,
    HolderName VARCHAR(255) NOT NULL,
    Balance DECIMAL(10,2) default 0.00
);

-- Keep a list of all the tags available
CREATE TABLE Tag (
    TagID INT PRIMARY KEY AUTO_INCREMENT,
    Name VARCHAR(50) NOT NULL UNIQUE
);

-- Links tags to pitches
CREATE TABLE PitchTag (
    PitchID INT,
    TagID INT,
    PRIMARY KEY (PitchID, TagID),
    FOREIGN KEY (PitchID) REFERENCES Pitch(PitchID) ON DELETE CASCADE,
    FOREIGN KEY (TagID) REFERENCES Tag(TagID) ON DELETE CASCADE
);

-- Index for faster filtering by tag
CREATE INDEX idx_tagid ON PitchTag(TagID);

ALTER TABLE Investor
ADD COLUMN Address VARCHAR(255),
ADD COLUMN DateOfBirth DATE,
ADD COLUMN Nationality VARCHAR(100),
ADD COLUMN PreferredCurrency VARCHAR(50);

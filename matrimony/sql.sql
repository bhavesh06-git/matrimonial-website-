/*
-- FILE: matrimony_schema.sql
-- This file contains the complete database structure for the entire project.
-- Run this in your database (e.g., phpMyAdmin) to create all necessary tables.
*/

CREATE DATABASE IF NOT EXISTS `matrimony_db`;
USE `matrimony_db`;

--
-- Table 1: `users` (Your existing table)
-- Stores all primary user profile information.
--
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `gender` enum('Male','Female','Other') NOT NULL,
  `dob` date NOT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `religion` varchar(50) DEFAULT NULL,
  `caste` varchar(50) DEFAULT NULL,
  `education` varchar(100) DEFAULT NULL,
  `occupation` varchar(100) DEFAULT NULL,
  `annual_income` varchar(50) DEFAULT NULL,
  `marital_status` enum('Never Married','Divorced','Widowed','Awaiting Divorce') NOT NULL,
  `height_cm` int(11) DEFAULT NULL,
  `about_me` text DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT 'default.png',
  `registration_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `profile_status` enum('Active','Inactive','Pending') NOT NULL DEFAULT 'Active',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Table 2: `admin_users` (NEW)
-- For a secure admin panel login, separate from regular users.
--
DROP TABLE IF EXISTS `admin_users`;
CREATE TABLE `admin_users` (
  `admin_id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `last_login` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`admin_id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert a default admin user
-- Username: admin
-- Password: password123
INSERT INTO `admin_users` (`username`, `password`, `full_name`) VALUES
('admin', '$2y$10$N.iP7a4.X.Gf9.Yg.Gf9.O/U.j.Yg.Gf9.O/U.j.Yg.Gf9.O/', 'Default Admin');

--
-- Table 3: `partner_preferences` (NEW)
-- Stores what each user is looking for in a partner.
--
DROP TABLE IF EXISTS `partner_preferences`;
CREATE TABLE `partner_preferences` (
  `preference_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `min_age` int(11) DEFAULT 21,
  `max_age` int(11) DEFAULT 35,
  `gender` enum('Male','Female','Other') DEFAULT NULL,
  `religion` varchar(255) DEFAULT NULL,
  `caste` varchar(255) DEFAULT NULL,
  `education` varchar(255) DEFAULT NULL,
  `occupation` varchar(255) DEFAULT NULL,
  `country` varchar(255) DEFAULT NULL,
  `marital_status` varchar(255) DEFAULT NULL,
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`preference_id`),
  UNIQUE KEY `user_id` (`user_id`),
  CONSTRAINT `fk_pref_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Table 4: `interests` (NEW)
-- Tracks "likes" or "interests" sent from one user to another.
--
DROP TABLE IF EXISTS `interests`;
CREATE TABLE `interests` (
  `interest_id` int(11) NOT NULL AUTO_INCREMENT,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `status` enum('Sent','Accepted','Declined') NOT NULL DEFAULT 'Sent',
  `sent_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`interest_id`),
  UNIQUE KEY `unique_interest` (`sender_id`,`receiver_id`),
  CONSTRAINT `fk_interest_sender` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_interest_receiver` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Table 5: `matches` (NEW)
-- Created when an interest is 'Accepted'. This unlocks the chat feature.
--
DROP TABLE IF EXISTS `matches`;
CREATE TABLE `matches` (
  `match_id` int(11) NOT NULL AUTO_INCREMENT,
  `user1_id` int(11) NOT NULL,
  `user2_id` int(11) NOT NULL,
  `matched_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`match_id`),
  UNIQUE KEY `unique_match` (`user1_id`,`user2_id`),
  CONSTRAINT `fk_match_user1` FOREIGN KEY (`user1_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_match_user2` FOREIGN KEY (`user2_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Table 6: `chat_messages` (NEW)
-- Stores all chat messages for the simple PHP-based chat system.
--
DROP TABLE IF EXISTS `chat_messages`;
CREATE TABLE `chat_messages` (
  `message_id` int(11) NOT NULL AUTO_INCREMENT,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `message_text` text NOT NULL,
  `sent_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`message_id`),
  KEY `idx_chat_conversation` (`sender_id`,`receiver_id`),
  CONSTRAINT `fk_chat_sender` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_chat_receiver` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Table 7: `profile_views` (NEW - Optional Bonus Feature)
-- Tracks who viewed whose profile.
--
DROP TABLE IF EXISTS `profile_views`;
CREATE TABLE `profile_views` (
  `view_id` int(11) NOT NULL AUTO_INCREMENT,
  `viewer_id` int(11) NOT NULL,
  `profile_id` int(11) NOT NULL,
  `viewed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`view_id`),
  KEY `idx_viewer` (`viewer_id`),
  KEY `idx_profile` (`profile_id`),
  CONSTRAINT `fk_view_viewer` FOREIGN KEY (`viewer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_view_profile` FOREIGN KEY (`profile_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



INSERT INTO `users` 
(`email`, `password`, `first_name`, `last_name`, `gender`, `dob`, `phone_number`, `religion`, `caste`, `education`, `occupation`, `annual_income`, `marital_status`, `height_cm`, `about_me`, `city`, `state`, `country`, `profile_image`, `profile_status`) 
VALUES
(
  'priya.sharma@example.com', 
  '$2y$10$N.iP7a4.X.Gf9.Yg.Gf9.O/U.j.Yg.Gf9.O/U.j.Yg.Gf9.O/', 
  'Priya', 'Sharma', 'Female', '1998-05-12', 
  '9876543210', 'Hindu', 'Brahmin', 'M.Tech', 'Software Engineer', '15-20 Lakhs', 'Never Married', 162, 
  'I am a software engineer living in Bangalore. I enjoy trekking, reading, and exploring new cafes. Looking for an understanding and supportive partner.', 
  'Bangalore', 'Karnataka', 'India', 
  'https://images.unsplash.com/photo-1494790108377-be9c29b29330?q=80&w=500&auto=format&fit=crop', 'Active'
),
(
  'rohan.mehta@example.com', 
  '$2y$10$N.iP7a4.X.Gf9.Yg.Gf9.O/U.j.Yg.Gf9.O/U.j.Yg.Gf9.O/', 
  'Rohan', 'Mehta', 'Male', '1995-02-20', 
  '9123456780', 'Hindu', 'Bania', 'MBA', 'Management Consultant', '20-30 Lakhs', 'Never Married', 178, 
  'Mumbai-based consultant, ambitious and travel-loving. I value honesty and a good sense of humor. My weekends are for cricket or a good movie.', 
  'Mumbai', 'Maharashtra', 'India', 
  'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?q=80&w=500&auto=format&fit=crop', 'Active'
),
(
  'aisha.khan@example.com', 
  '$2y$10$N.iP7a4.X.Gf9.Yg.Gf9.O/U.j.Yg.Gf9.O/U.j.Yg.Gf9.O/', 
  'Aisha', 'Khan', 'Female', '1999-11-30', 
  '9988776655', 'Muslim', 'Sunni', 'MBBS', 'Doctor', '15-20 Lakhs', 'Never Married', 158, 
  'I am a doctor, currently working in Delhi. I am family-oriented, compassionate, and looking for someone with a similar mindset.', 
  'New Delhi', 'Delhi', 'India', 
  'https://images.unsplash.com/photo-1529626455594-4ff0802cfb7e?q=80&w=500&auto=format&fit=crop', 'Active'
),
(
  'karan.singh@example.com', 
  '$2y$10$N.iP7a4.X.Gf9.Yg.Gf9.O/U.j.Yg.Gf9.O/U.j.Yg.Gf9.O/', 
  'Karan', 'Singh', 'Male', '1994-08-15', 
  '9765432109', 'Sikh', 'Jat', 'B.E.', 'Civil Engineer', '10-15 Lakhs', 'Never Married', 183, 
  'Working in Chandigarh as a civil engineer. I am an optimist, enjoy fitness, and love dogs. Looking for a genuine connection.', 
  'Chandigarh', 'Punjab', 'India', 
  'https://images.unsplash.com/photo-1539571696357-5a69c17a67c6?q=80&w=500&auto=format&fit=crop', 'Active'
),
(
  'sanjana.reddy@example.com', 
  '$2y$10$N.iP7a4.X.Gf9.Yg.Gf9.O/U.j.Yg.Gf9.O/U.j.Yg.Gf9.O/', 
  'Sanjana', 'Reddy', 'Female', '1997-01-25', 
  '9654321098', 'Hindu', 'Reddy', 'M.S. (USA)', 'Data Scientist', '30+ Lakhs', 'Never Married', 165, 
  'Just moved back to Hyderabad after my Masters in the US. I am a blend of modern and traditional values. Love to code and cook.', 
  'Hyderabad', 'Telangana', 'India', 
  'https://images.unsplash.com/photo-1508214751196-bcfd4ca60f91?q=80&w=500&auto=format&fit=crop', 'Active'
),
(
  'vikram.nair@example.com', 
  '$2y$10$N.iP7a4.X.Gf9.Yg.Gf9.O/U.j.Yg.Gf9.O/U.j.Yg.Gf9.O/', 
  'Vikram', 'Nair', 'Male', '1993-07-07', 
  '9543210987', 'Hindu', 'Nair', 'B.Arch', 'Architect', '10-15 Lakhs', 'Never Married', 175, 
  'Architect based in Kochi. I am creative, calm, and passionate about sustainable design. I enjoy photography and long drives.', 
  'Kochi', 'Kerala', 'India', 
  'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?q=80&w=500&auto=format&fit=crop', 'Active'
),
(
  'neha.jain@example.com', 
  '$2y$10$N.iP7a4.X.Gf9.Yg.Gf9.O/U.j.Yg.Gf9.O/U.j.Yg.Gf9.O/', 
  'Neha', 'Jain', 'Female', '1996-09-01', 
  '9432109876', 'Jain', 'Oswal', 'CA', 'Chartered Accountant', '15-20 Lakhs', 'Never Married', 160, 
  'I am a CA working with a top firm in Pune. I am diligent, sincere, and love to travel. Looking for an ambitious and like-minded partner.', 
  'Pune', 'Maharashtra', 'India', 
  'https://images.unsplash.com/photo-1580489944761-15a19d654956?q=80&w=500&auto=format&fit=crop', 'Active'
),
(
  'arjun.gupta@example.com', 
  '$2y$10$N.iP7a4.X.Gf9.Yg.Gf9.O/U.j.Yg.Gf9.O/U.j.Yg.Gf9.O/', 
  'Arjun', 'Gupta', 'Male', '1992-12-18', 
  '9321098765', 'Hindu', 'Bania', 'MBA', 'Product Manager', '30+ Lakhs', 'Never Married', 180, 
  'I am a Product Manager at a startup in Bangalore. I am driven, a problem-solver, and a huge foodie. Seeking an independent and intelligent partner.', 
  'Bangalore', 'Karnataka', 'India', 
  'https://images.unsplash.com/photo-1519085360753-af0119f7cbe7?q=80&w=500&auto=format&fit=crop', 'Active'
),
(
  'simran.kaur@example.com', 
  '$2y$10$N.iP7a4.X.Gf9.Yg.Gf9.O/U.j.Yg.Gf9.O/U.j.Yg.Gf9.O/', 
  'Simran', 'Kaur', 'Female', '1997-03-14', 
  '9210987654', 'Sikh', 'Khatri', 'B.Des', 'Fashion Designer', '7-10 Lakhs', 'Never Married', 168, 
  'Fashion designer from Delhi. I am creative, expressive, and love art. I am looking for a partner who is respectful, caring, and open-minded.', 
  'New Delhi', 'Delhi', 'India', 
  'https://images.unsplash.com/photo-1520466809213-7b9a56ad9542?q=80&w=500&auto=format&fit=crop', 'Active'
),
(
  'rahul.verma@example.com', 
  '$2y$10$N.iP7a4.X.Gf9.Yg.Gf9.O/U.j.Yg.Gf9.O/U.j.Yg.Gf9.O/', 
  'Rahul', 'Verma', 'Male', '1996-06-22', 
  '9109876543', 'Hindu', 'OBC', 'B.Tech', 'Software Developer', '10-15 Lakhs', 'Never Married', 173, 
  'I am a developer in Pune. Simple, down-to-earth guy. I enjoy gaming, coding, and street food. Looking for a simple and honest partner.', 
  'Pune', 'Maharashtra', 'India', 
  'https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?q=80&w=500&auto=format&fit=crop', 'Active'
),
(
  'ananya.joshi@example.com', 
  '$2y$10$N.iP7a4.X.Gf9.Yg.Gf9.O/U.j.Yg.Gf9.O/U.j.Yg.Gf9.O/', 
  'Ananya', 'Joshi', 'Female', '1998-04-05', 
  '9012345678', 'Hindu', 'Brahmin', 'M.A. (Psychology)', 'Psychologist', '7-10 Lakhs', 'Never Married', 163, 
  'I am a practicing psychologist in Mumbai. I am empathetic, a good listener, and value mental well-being. I enjoy yoga and meditation.', 
  'Mumbai', 'Maharashtra', 'India', 
  'https://images.unsplash.com/photo-1544005313-94ddf0286df2?q=80&w=500&auto=format&fit=crop', 'Active'
),
(
  'sidharth.mishra@example.com', 
  '$2y$10$N.iP7a4.X.Gf9.Yg.Gf9.O/U.j.Yg.Gf9.O/U.j.Yg.Gf9.O/', 
  'Sidharth', 'Mishra', 'Male', '1993-10-10', 
  '8901234567', 'Hindu', 'Brahmin', 'PhD', 'Professor', '10-15 Lakhs', 'Never Married', 177, 
  'Professor of Literature at a university in Kolkata. I am an avid reader, intellectual, and love deep conversations. Seeking a partner who shares my curiosity.', 
  'Kolkata', 'West Bengal', 'India', 
  'https://images.unsplash.com/photo-1522075469751-3a6694fb2f61?q=80&w=500&auto=format&fit=crop', 'Active'
),
(
  'diya.patel@example.com', 
  '$2y$10$N.iP7a4.X.Gf9.Yg.Gf9.O/U.j.Yg.Gf9.O/U.j.Yg.Gf9.O/', 
  'Diya', 'Patel', 'Female', '1999-07-21', 
  '8890123456', 'Hindu', 'Patel', 'BBA', 'Marketing Executive', '5-7 Lakhs', 'Never Married', 157, 
  'Working in Ahmedabad. I am cheerful, social, and love to dance. My family is very important to me. Looking for a partner who is fun-loving and family-oriented.', 
  'Ahmedabad', 'Gujarat', 'India', 
  'https://images.unsplash.com/photo-1517841905240-472988babdf9?q=80&w=500&auto=format&fit=crop', 'Active'
),
(
  'mohammed.ali@example.com', 
  '$2y$10$N.iP7a4.X.Gf9.Yg.Gf9.O/U.j.Yg.Gf9.O/U.j.Yg.Gf9.O/', 
  'Mohammed', 'Ali', 'Male', '1994-01-08', 
  '8789012345', 'Muslim', 'Shia', 'B.Tech', 'Data Analyst', '10-15 Lakhs', 'Never Married', 170, 
  'Data Analyst in Hyderabad. I am religious, respectful, and hardworking. I enjoy technology and spending time with my family.', 
  'Hyderabad', 'Telangana', 'India', 
  'https://images.unsplash.com/photo-1504257432389-52343af06ae3?q=80&w=500&auto=format&fit=crop', 'Active'
),
(
  'preeti.gomes@example.com', 
  '$2y$10$N.iP7a4.X.Gf9.Yg.Gf9.O/U.j.Yg.Gf9.O/U.j.Yg.Gf9.O/', 
  'Preeti', 'Gomes', 'Female', '1995-12-03', 
  '8678901234', 'Christian', 'Roman Catholic', 'M.A. (English)', 'Content Writer', '5-7 Lakhs', 'Never Married', 166, 
  'Content writer from Goa. I am a spiritual person, love beaches, music, and writing. Looking for a kind-hearted and simple man.', 
  'Goa', 'Goa', 'India', 
  'https://images.unsplash.com/photo-1516756587022-7891ad56a8cd?q=80&w=500&auto=format&fit=crop', 'Active'
),
(
  'rohit.yadav@example.com', 
  '$2y$10$N.iP7a4.X.Gf9.Yg.Gf9.O/U.j.Yg.Gf9.O/U.j.Yg.Gf9.O/', 
  'Rohit', 'Yadav', 'Male', '1996-03-30', 
  '8567890123', 'Hindu', 'Yadav', 'B.Com', 'Banker', '7-10 Lakhs', 'Never Married', 172, 
  'Working in a private bank in Lucknow. I am practical, stable, and a bit of an introvert. I like to live a balanced life.', 
  'Lucknow', 'Uttar Pradesh', 'India', 
  'https://images.unsplash.com/photo-1521119989659-a83eee488004?q=80&w=500&auto=format&fit=crop', 'Active'
),
(
  'chhavi.gupta@example.com', 
  '$2y$10$N.iP7a4.X.Gf9.Yg.Gf9.O/U.j.Yg.Gf9.O/U.j.Yg.Gf9.O/', 
  'Chhavi', 'Gupta', 'Female', '1997-09-17', 
  '8456789012', 'Hindu', 'Bania', 'MBA', 'HR Manager', '10-15 Lakhs', 'Never Married', 155, 
  'HR Manager in Gurgaon. I am an extrovert, love socializing, and am passionate about my career. Looking for an equally ambitious partner.', 
  'Gurgaon', 'Haryana', 'India', 
  'https://images.unsplash.com/photo-1552695841-f7ba30b6b23a?q=80&w=500&auto=format&fit=crop', 'Active'
),
(
  'vivek.kumar@example.com', 
  '$2y$10$N.iP7a4.X.Gf9.Yg.Gf9.O/U.j.Yg.Gf9.O/U.j.Yg.Gf9.O/', 
  'Vivek', 'Kumar', 'Male', '1994-11-05', 
  '8345678901', 'Hindu', 'Kshatriya', 'M.Sc (Physics)', 'Civil Servant', '15-20 Lakhs', 'Never Married', 178, 
  'Posted in Jaipur. I am a civil servant. I believe in discipline, integrity, and public service. I enjoy reading history and playing badminton.', 
  'Jaipur', 'Rajasthan', 'India', 
  'https://images.unsplash.com/photo-1557862921-37829c790f19?q=80&w=500&auto=format&fit=crop', 'Active'
),
(
  'deepa.murthy@example.com', 
  '$2y$10$N.iP7a4.X.Gf9.Yg.Gf9.O/U.j.Yg.Gf9.O/U.j.Yg.Gf9.O/', 
  'Deepa', 'Murthy', 'Female', '1996-02-14', 
  '8234567890', 'Hindu', 'Brahmin', 'B.Tech', 'UI/UX Designer', '10-15 Lakhs', 'Never Married', 164, 
  'UI/UX Designer in Chennai. I love all things design, art, and aesthetics. I am also a trained classical dancer. Looking for a creative and supportive partner.', 
  'Chennai', 'Tamil Nadu', 'India', 
  'https://images.unsplash.com/photo-1542103749-8ef59b94f47e?q=80&w=500&auto=format&fit=crop', 'Active'
),
(
  'aditya.roy@example.com', 
  '$2y$10$N.iP7a4.X.Gf9.Yg.Gf9.O/U.j.Yg.Gf9.O/U.j.Yg.Gf9.O/', 
  'Aditya', 'Roy', 'Male', '1992-05-28', 
  '8123456789', 'Hindu', 'Kayastha', 'LLM', 'Lawyer', '20-30 Lakhs', 'Never Married', 179, 
  'Corporate lawyer working in Delhi. I am articulate, logical, and have a strong sense of justice. I enjoy debates and playing chess.', 
  'New Delhi', 'Delhi', 'India', 
  'https://images.unsplash.com/photo-1542206395-9feb3edaa68d?q=80&w=500&auto=format&fit=crop', 'Active'
),
(
  'sonal.agarwal@example.com', 
  '$2y$10$N.iP7a4.X.Gf9.Yg.Gf9.O/U.j.Yg.Gf9.O/U.j.Yg.Gf9.O/', 
  'Sonal', 'Agarwal', 'Female', '1995-10-02', 
  '9876501234', 'Hindu', 'Marwari', 'MBA', 'Brand Manager', '15-20 Lakhs', 'Never Married', 160, 
  'I am a Brand Manager in Mumbai. I am ambitious, outgoing, and love to explore new cultures. Looking for someone who is as passionate about life as I am.', 
  'Mumbai', 'Maharashtra', 'India', 
  'https://images.unsplash.com/photo-1534528741775-53994a69daeb?q=80&w=500&auto=format&fit=crop', 'Active'
),
(
  'james.dsouza@example.com', 
  '$2y$10$N.iP7a4.X.Gf9.Yg.Gf9.O/U.j.Yg.Gf9.O/U.j.Yg.Gf9.O/', 
  'James', 'DSouza', 'Male', '1993-04-11', 
  '9765401234', 'Christian', 'Roman Catholic', 'B.Sc (Hospitality)', 'Hotel Manager', '10-15 Lakhs', 'Never Married', 176, 
  'I manage a luxury hotel in Goa. I am a people person, very adjusting, and love my job. I enjoy good food and music.', 
  'Goa', 'Goa', 'India', 
  'https://images.unsplash.com/photo-1543610892-0b1f7e6d8ac1?q=80&w=500&auto=format&fit=crop', 'Active'
),
(
  'tanvi.bose@example.com', 
  '$2y$10$N.iP7a4.X.Gf9.Yg.Gf9.O/U.j.Yg.Gf9.O/U.j.Yg.Gf9.O/', 
  'Tanvi', 'Bose', 'Female', '1997-08-08', 
  '9654301234', 'Hindu', 'Kayastha', 'B.A. (Journalism)', 'Journalist', '7-10 Lakhs', 'Never Married', 161, 
  'Journalist based in Kolkata. I am curious, outspoken, and passionate about storytelling. Looking for an honest and open-minded partner.', 
  'Kolkata', 'West Bengal', 'India', 
  'https://images.unsplash.com/photo-1593104547489-5cfb3839a3b5?q=80&w=500&auto=format&fit=crop', 'Active'
),
(
  'raj.patil@example.com', 
  '$2y$10$N.iP7a4.X.Gf9.Yg.Gf9.O/U.j.Yg.Gf9.O/U.j.Yg.Gf9.O/', 
  'Raj', 'Patil', 'Male', '1994-06-19', 
  '9543201234', 'Hindu', 'Maratha', 'M.Tech', 'Data Engineer', '20-30 Lakhs', 'Never Married', 174, 
  'Data Engineer in Pune. I am practical, a tech enthusiast, and enjoy trekking on weekends. Looking for a simple, caring, and educated partner.', 
  'Pune', 'Maharashtra', 'India', 
  'https://images.unsplash.com/photo-1560250097-0b93528c311a?q=80&w=500&auto=format&fit=crop', 'Active'
),
(
  'ishita.dubey@example.com', 
  '$2y$10$N.iP7a4.X.Gf9.Yg.Gf9.O/U.j.Yg.Gf9.O/U.j.Yg.Gf9.O/', 
  'Ishita', 'Dubey', 'Female', '1999-01-01', 
  '9432101234', 'Hindu', 'Brahmin', 'B.Com', 'Analyst', '5-7 Lakhs', 'Never Married', 159, 
  'Working as an analyst in Bangalore. I am a mix of modern and traditional values. I love painting, dancing, and spending time with family.', 
  'Bangalore', 'Karnataka', 'India', 
  'https://images.unsplash.com/photo-1531746020798-e6953c6e8e04?q=80&w=500&auto=format&fit=crop', 'Active'
),
(
  'samir.shaikh@example.com', 
  '$2y$10$N.iP7a4.X.Gf9.Yg.Gf9.O/U.j.Yg.Gf9.O/U.j.Yg.Gf9.O/', 
  'Samir', 'Shaikh', 'Male', '1995-09-12', 
  '9321001234', 'Muslim', 'Sunni', 'MBA', 'Entrepreneur', '20-30 Lakhs', 'Never Married', 180, 
  'I run my own tech startup in Bangalore. I am ambitious, hardworking, and love building new things. Looking for a partner who is supportive and intelligent.', 
  'Bangalore', 'Karnataka', 'India', 
  'https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?q=80&w=500&auto=format&fit=crop', 'Active'
),
(
  'riya.prakash@example.com', 
  '$2y$10$N.iP7a4.X.Gf9.Yg.Gf9.O/U.j.Yg.Gf9.O/U.j.Yg.Gf9.O/', 
  'Riya', 'Prakash', 'Female', '1996-07-30', 
  '9210901234', 'Hindu', 'Kshatriya', 'M.Sc (Biotech)', 'Research Scientist', '7-10 Lakhs', 'Never Married', 167, 
  'I am a research scientist in Hyderabad. I am curious, dedicated to my work, and love to read. Seeking an educated and well-settled partner.', 
  'Hyderabad', 'Telangana', 'India', 
  'https://images.unsplash.com/photo-1524504388940-b1c1722653e1?q=80&w=500&auto=format&fit=crop', 'Active'
),
(
  'alan.mathew@example.com', 
  '$2y$10$N.iP7a4.X.Gf9.Yg.Gf9.O/U.j.Yg.Gf9.O/U.j.Yg.Gf9.O/', 
  'Alan', 'Mathew', 'Male', '1994-03-02', 
  '9109801234', 'Christian', 'Syro-Malabar', 'MS (CS)', 'Software Engineer', '30+ Lakhs', 'Never Married', 177, 
  'Software Engineer working in Toronto. I am well-settled, love to travel, and am a big-time foodie. Looking for a partner willing to relocate.', 
  'Toronto', 'Ontario', 'Canada', 
  'https://images.unsplash.com/photo-1520341280432-4740d427c80a?q=80&w=500&auto=format&fit=crop', 'Active'
),
(
  'benita.george@example.com', 
  '$2y$10$N.iP7a4.X.Gf9.Yg.Gf9.O/U.j.Yg.Gf9.O/U.j.Yg.Gf9.O/', 
  'Benita', 'George', 'Female', '1997-06-15', 
  '9012301234', 'Christian', 'Orthodox', 'M.Arch', 'Architect', '7-10 Lakhs', 'Never Married', 165, 
  'Architect based in Kochi. I am passionate about art, design, and my faith. Looking for a God-fearing man with good family values.', 
  'Kochi', 'Kerala', 'India', 
  'https://images.unsplash.com/photo-1558222218-b03d3cec3395?q=80&w=500&auto=format&fit=crop', 'Active'
),
(
  'jay.doshi@example.com', 
  '$2y$10$N.iP7a4.X.Gf9.Yg.Gf9.O/U.j.Yg.Gf9.O/U.j.Yg.Gf9.O/', 
  'Jay', 'Doshi', 'Male', '1995-08-25', 
  '8901201234', 'Jain', 'Digambar', 'MBA', 'Investment Banker', '20-30 Lakhs', 'Never Married', 176, 
  'Investment Banker in Mumbai. Life is fast-paced, but I am grounded. I value family, hard work, and financial stability.', 
  'Mumbai', 'Maharashtra', 'India', 
  'https://images.unsplash.com/photo-1548142813-c348350df52b?q=80&w=500&auto=format&fit=crop', 'Active'
),
(
  'kavya.iyer@example.com', 
  '$2y$10$N.iP7a4.X.Gf9.Yg.Gf9.O/U.j.Yg.Gf9.O/U.j.Yg.Gf9.O/', 
  'Kavya', 'Iyer', 'Female', '1998-11-11', 
  '8789001234', 'Hindu', 'Brahmin (Iyer)', 'M.A. (Music)', 'Music Teacher', '5-7 Lakhs', 'Never Married', 160, 
  'I am a classical music teacher in Chennai. I am traditional, soft-spoken, and deeply connected to my roots and culture.', 
  'Chennai', 'Tamil Nadu', 'India', 
  'https://images.unsplash.com/photo-1531123414780-f74242c2b052?q=80&w=500&auto=format&fit=crop', 'Active'
),
(
  'aman.sareen@example.com', 
  '$2y$10$N.iP7a4.X.Gf9.Yg.Gf9.O/U.j.Yg.Gf9.O/U.j.Yg.Gf9.O/', 
  'Aman', 'Sareen', 'Male', '1993-02-09', 
  '8678901234', 'Hindu', 'Khatri', 'B.Tech', 'Software Engineer', '30+ Lakhs', 'Never Married', 182, 
  'Working for a FAANG company in London. I am ambitious and well-settled, but miss my Indian roots. Looking for a partner to share this journey with.', 
  'London', 'N/A', 'United Kingdom', 
  'https://images.unsplash.com/photo-1564564321837-a57b7070ac5c?q=80&w=500&auto=format&fit=crop', 'Active'
),
(
  'meghna.rathore@example.com', 
  '$2y$10$N.iP7a4.X.Gf9.Yg.Gf9.O/U.j.Yg.Gf9.O/U.j.Yg.Gf9.O/', 
  'Meghna', 'Rathore', 'Female', '1996-05-23', 
  '8567890123', 'Hindu', 'Rajput', 'M.A. (History)', 'Museum Curator', '5-7 Lakhs', 'Never Married', 169, 
  'Living in Jaipur, surrounded by history. I am elegant, well-read, and have a calm personality. I appreciate art, culture, and heritage.', 
  'Jaipur', 'Rajasthan', 'India', 
  'https://images.unsplash.com/photo-1610216705422-caa3fcb6d158?q=80&w=500&auto=format&fit=crop', 'Active'
),
(
  'tarun.biswas@example.com', 
  '$2y$10$N.iP7a4.X.Gf9.Yg.Gf9.O/U.j.Yg.Gf9.O/U.j.Yg.Gf9.O/', 
  'Tarun', 'Biswas', 'Male', '1995-01-30', 
  '8456789012', 'Hindu', 'Kayastha', 'B.Sc (Animation)', 'VFX Artist', '10-15 Lakhs', 'Never Married', 173, 
  'VFX Artist in Mumbai. My work is my passion. I am creative, a bit of a night owl, and love movies and graphic novels.', 
  'Mumbai', 'Maharashtra', 'India', 
  'https://images.unsplash.com/photo-1547425260-76bcadfb4f2c?q=80&w=500&auto=format&fit=crop', 'Active'
),
(
  'anika.sharma@example.com', 
  '$2y$10$N.iP7a4.X.Gf9.Yg.Gf9.O/U.j.Yg.Gf9.O/U.j.Yg.Gf9.O/', 
  'Anika', 'Sharma', 'Female', '1997-04-12', 
  '8345678901', 'Hindu', 'Brahmin', 'MBA', 'Marketing Head', '20-30 Lakhs', 'Divorced', 163, 
  'I am a marketing head, independent and strong-willed. I have a 3-year-old daughter who is my world. Looking for a mature and understanding partner.', 
  'Bangalore', 'Karnataka', 'India', 
  'https://images.unsplash.com/photo-1525134479668-1bee5c7c6845?q=80&w=500&auto=format&fit=crop', 'Active'
),
(
  'nikhil.chavan@example.com', 
  '$2y$10$N.iP7a4.X.Gf9.Yg.Gf9.O/U.j.Yg.Gf9.O/U.j.Yg.Gf9.O/', 
  'Nikhil', 'Chavan', 'Male', '1993-09-03', 
  '8234567890', 'Hindu', 'Maratha', 'B.E.', 'Mechanical Engineer', '7-10 Lakhs', 'Never Married', 175, 
  'Working in an automotive company in Pune. I am practical, reliable, and love bikes. My weekends are for riding or spending time with friends.', 
  'Pune', 'Maharashtra', 'India', 
  'https://images.unsplash.com/photo-1519699047748-6f52add2c75a?q=80&w=500&auto=format&fit=crop', 'Active'
),
(
  'fatima.hussain@example.com', 
  '$2y$10$N.iP7a4.X.Gf9.Yg.Gf9.O/U.j.Yg.Gf9.O/U.j.Yg.Gf9.O/', 
  'Fatima', 'Hussain', 'Female', '1998-02-28', 
  '8123456789', 'Muslim', 'Shia', 'M.Com', 'Bank PO', '7-10 Lakhs', 'Never Married', 158, 
  'I work at a national bank in Lucknow. I am simple, religious, and value my family. I enjoy cooking and poetry.', 
  'Lucknow', 'Uttar Pradesh', 'India', 
  'https://images.unsplash.com/photo-1567532939604-b675b0504653?q=80&w=500&auto=format&fit=crop', 'Active'
),
(
  'sandeep.singhania@example.com', 
  '$2y$10$N.iP7a4.X.Gf9.Yg.Gf9.O/U.j.Yg.Gf9.O/U.j.Yg.Gf9.O/', 
  'Sandeep', 'Singhania', 'Male', '1992-07-16', 
  '9876501234', 'Hindu', 'Marwari', 'CA', 'Business Owner', '30+ Lakhs', 'Never Married', 178, 
  'I manage my family business in Kolkata. I am business-minded, ambitious, and believe in hard work. Looking for a partner who can be a part of my family.', 
  'Kolkata', 'West Bengal', 'India', 
  'https://images.unsplash.com/photo-1566753323558-f4e0952af115?q=80&w=500&auto=format&fit=crop', 'Active'
),
(
  'pooja.thakur@example.com', 
  '$2y$10$N.iP7a4.X.Gf9.Yg.Gf9.O/U.j.Yg.Gf9.O/U.j.Yg.Gf9.O/', 
  'Pooja', 'Thakur', 'Female', '1994-11-20', 
  '9765401234', 'Hindu', 'Rajput', 'B.A.', 'Homemaker', 'N/A', 'Widowed', 165, 
  'I am a simple, god-fearing woman. My previous partner passed away a few years ago. I am looking for a kind and mature companion to start a new life with.', 
  'Jaipur', 'Rajasthan', 'India', 
  'https://images.unsplash.com/photo-15411707968-9635e2380a04?q=80&w=500&auto=format&fit=crop', 'Active'
),
(
  'isha.singh@example.com', 
  '$2y$10$N.iP7a4.X.Gf9.Yg.Gf9.O/U.j.Yg.Gf9.O/U.j.Yg.Gf9.O/', 
  'Isha', 'Singh', 'Female', '1999-08-10', 
  '9654301234', 'Hindu', 'Kshatriya', 'B.Tech', 'Software Tester', '7-10 Lakhs', 'Never Married', 162, 
  'I am a software tester in Noida. I am practical, career-oriented, and love to travel. My weekends are for friends and family.', 
  'Noida', 'Uttar Pradesh', 'India', 
  'https://images.unsplash.com/photo-1531384261419-58a98078e370?q=80&w=500&auto=format&fit=crop', 'Active'
),
(
  'ethan.fernandes@example.com', 
  '$2y$10$N.iP7a4.X.Gf9.Yg.Gf9.O/U.j.Yg.Gf9.O/U.j.Yg.Gf9.O/', 
  'Ethan', 'Fernandes', 'Male', '1995-04-04', 
  '9543201234', 'Christian', 'Catholic', 'B.Sc (CompSci)', 'System Administrator', '15-20 Lakhs', 'Never Married', 180, 
  'System Admin in New York. Born in Mumbai, moved here for work. I am easy-going, love technology, and am a big NBA fan.', 
  'New York', 'NY', 'USA', 
  'https://images.unsplash.com/photo-1518020382113-a7e8fc38eac9?q=80&w=500&auto=format&fit=crop', 'Active'
);




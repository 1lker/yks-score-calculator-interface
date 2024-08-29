# YKS Score Calculator and Exam Tracking System

## Table of Contents
1. [Project Overview](#project-overview)
2. [File Structure](#file-structure)
3. [File Descriptions](#file-descriptions)
4. [Key Features](#key-features)
5. [Technical Details](#technical-details)
6. [Installation](#installation)
7. [Security Measures](#security-measures)
8. [Future Improvements](#future-improvements)
9. [Contributing](#contributing)
10. [License](#license)

## Project Overview

This project is a web application for calculating scores and tracking exams for the Turkish Higher Education Institutions Examination (YKS). Students can input their exam results, calculate scores, view past exams, and track their statistics.

## File Structure
.
├── animations.js
├── calculate.php
├── connect.php
├── debug.log
├── exam_save_log.txt
├── exams.json
├── index.php
├── my_exams.php
├── save_exam.php
├── script.js
├── statistics.php
├── styles-my-exams.css
├── styles-statistics.css
├── styles.css
└── target.php
Copy
## File Descriptions

### PHP Files

- `index.php`: Main page containing the YKS score calculation form.
- `calculate.php`: Calculates exam scores.
- `connect.php`: Manages database connection.
- `my_exams.php`: Lists saved exams.
- `save_exam.php`: Saves new exam results.
- `statistics.php`: Displays user's exam statistics.
- `target.php`: Goal setting and tracking page.

### JavaScript Files

- `animations.js`: Manages page animations.
- `script.js`: Main JavaScript file handling form operations, AJAX requests, and dynamic content updates.

### CSS Files

- `styles.css`: Main stylesheet.
- `styles-my-exams.css`: Custom styles for the My Exams page.
- `styles-statistics.css`: Custom styles for the Statistics page.

### Other Files

- `debug.log`: Debugging log.
- `exam_save_log.txt`: Log for exam saving operations.
- `exams.json`: JSON file containing sample exam data.

## Key Features

1. YKS score calculation
2. Saving exam results
3. Viewing past exams
4. Statistical analysis
5. Goal setting and tracking

## Technical Details

### Database Structure

The project uses a MySQL database. The main table is `wp_yks_exams`, which stores exam results and user information.

### API Endpoints

1. `calculate.php`: Receives exam results via POST request and returns calculated scores.
2. `save_exam.php`: Saves new exam results via POST request.

### JavaScript Functions

- `updateNetScores()`: Updates net scores
- `displayResults()`: Displays calculated results
- `saveExam()`: Saves exam results
- `updateChart()`: Updates the result chart

### PHP Functions

- `calculateNet()`: Calculates net score
- `calculateTYTNet()`: Calculates TYT net score
- `calculateAYTSayNet()`: Calculates AYT Quantitative net score
- `calculateAYTSozNet()`: Calculates AYT Verbal net score

## Installation

1. Upload files to your web server.
2. Update database information in `connect.php`.
3. Create the `wp_yks_exams` table in the database.
4. Open `index.php` in a web browser.

CREATE TABLE unverified_companies (
    company_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    username VARCHAR(100) NOT NULL UNIQUE,
    email VARCHAR(150) NOT NULL UNIQUE,
    contact_num VARCHAR(20),
    address TEXT,
    website VARCHAR(255),
    password VARCHAR(255) NOT NULL,
    id_img VARCHAR(255),
    moa VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE unverified_students (
    student_id INT AUTO_INCREMENT PRIMARY KEY,
    id_num VARCHAR(50) NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    course VARCHAR(100) NOT NULL,
    contact_num VARCHAR(20),
    password VARCHAR(255) NOT NULL,
    company_name VARCHAR (150) NOT NULL,
    cor VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE verified_companies (
    company_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    username VARCHAR(100) NOT NULL,  
    email VARCHAR(150) NOT NULL,
    contact_num VARCHAR(20),
    address TEXT NOT NULL,
    website VARCHAR(255) NOT NULL,
    password VARCHAR(255) NOT NULL,
    id_img VARCHAR(255),
    moa VARCHAR(255),
    profile_pic VARCHAR(255) DEFAULT NULL,
    verified_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE verified_students (
    student_id INT AUTO_INCREMENT PRIMARY KEY,
    id_num VARCHAR(50) NOT NULL UNIQUE,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL,
    contact_num VARCHAR(20) NOT NULL,
    course VARCHAR(100) NOT NULL,
    specialization VARCHAR(100) NOT NULL,
    company_id INT,
    password VARCHAR(255),
    cor VARCHAR(255),
    profile_pic VARCHAR(255) DEFAULT NULL,
    verified_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_student_company FOREIGN KEY (company_id) REFERENCES verified_companies(company_id) ON DELETE SET NULL ON UPDATE CASCADE
);

CREATE TABLE student_evaluations (
    eval_id INT AUTO_INCREMENT PRIMARY KEY,
    company_id INT NOT NULL,
    student_id INT NOT NULL,
    evaluation_rank INT NOT NULL CHECK (evaluation_rank BETWEEN 0 AND 100),
    performance_score ENUM('Good', 'Bad') GENERATED ALWAYS AS (
        CASE 
            WHEN evaluation_rank >= 51 THEN 'Good'
            ELSE 'Bad'
        END
    ) STORED,
    date_issued TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES verified_companies(company_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (student_id) REFERENCES verified_students(student_id) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE job_opportunities (
    job_id INT AUTO_INCREMENT PRIMARY KEY,
    job_title VARCHAR(100) NOT NULL,
    job_location VARCHAR(100) NOT NULL,
    job_setup VARCHAR(100) NOT NULL,
    job_tags VARCHAR(100) NOT NULL,
    job_pay_min INT NOT NULL,
    job_pay_max INT NOT NULL,
    job_requirements TEXT NOT NULL,
    job_responsibilities TEXT NOT NULL,
    date_sent TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    company_id INT NOT NULL,
    FOREIGN KEY (company_id) REFERENCES verified_companies(company_id) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE ojt_progress (
    ojtprog_id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    FOREIGN KEY (student_id) REFERENCES verified_students(student_id) ON DELETE CASCADE ON UPDATE CASCADE,
    done_hours INT NOT NULL,
    required_hours INT NOT NULL 
);

CREATE TABLE notifications (
    notif_id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    FOREIGN KEY (student_id) REFERENCES verified_students(student_id) ON DELETE CASCADE ON UPDATE CASCADE,
    title TEXT NOT NULL,
    description TEXT NOT NULL,
    date_sent TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deadline DATE NULL
);

CREATE TABLE submission_reports (
    report_id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    report_file VARCHAR(255) NOT NULL, 
    date_sent TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES verified_students(student_id) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    password VARCHAR(255) NOT NULL,
    profile_pic VARCHAR(255) DEFAULT NULL
);
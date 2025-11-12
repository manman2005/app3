-- Updated Database Schema for Thai Timetable Management System
-- ระบบจัดตารางเรียนอัตโนมัติ

CREATE DATABASE IF NOT EXISTS timetable_db;
USE timetable_db;

-- ============================================
-- 1. ข้อมูลผู้ดูแลระบบ (Admin Users)
-- ============================================
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('admin', 'teacher', 'student') DEFAULT 'admin',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ============================================
-- 2. ข้อมูลวัน (Days)
-- ============================================
CREATE TABLE IF NOT EXISTS days (
    id INT AUTO_INCREMENT PRIMARY KEY,
    day_code VARCHAR(10) UNIQUE NOT NULL,   
    day_name VARCHAR(50) NOT NULL,
    day_order INT NOT NULL COMMENT '1=จันทร์, 2=อังคาร, ...',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================
-- 3. ข้อมูลเวลาเรียน (Time Slots)
-- ============================================
CREATE TABLE IF NOT EXISTS time_slots (
    id INT AUTO_INCREMENT PRIMARY KEY,
    time_code VARCHAR(10) UNIQUE NOT NULL,
    time_range VARCHAR(50) NOT NULL COMMENT 'เช่น 08.00-09.00',
    time_order INT NOT NULL COMMENT 'ลำดับคาบเรียน',
    notes TEXT COMMENT 'หมายเหตุ เช่น พักรับประทานอาหารกลางวัน',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================
-- 4. ข้อมูลประเภทวิชา (Subject Types)
-- ============================================
CREATE TABLE IF NOT EXISTS subject_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type_code VARCHAR(10) UNIQUE NOT NULL COMMENT '01, 09, ...',
    type_name VARCHAR(100) NOT NULL COMMENT 'เช่น ประเภทวิชาบริหารธุรกิจ',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================
-- 5. ข้อมูลสาขาวิชา (Majors)
-- ============================================
CREATE TABLE IF NOT EXISTS majors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    major_code VARCHAR(10) UNIQUE NOT NULL COMMENT '01, 02, ..., 22',
    major_name VARCHAR(100) NOT NULL COMMENT 'เช่น การบัญชี, เทคโนโลยีสารสนเทศ',
    subject_type_id INT,
    FOREIGN KEY (subject_type_id) REFERENCES subject_types(id) ON DELETE SET NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================
-- 6. ข้อมูลกลุ่มเรียน (Class Groups)
-- ============================================
CREATE TABLE IF NOT EXISTS class_groups (
    id INT AUTO_INCREMENT PRIMARY KEY,
    group_code VARCHAR(20) UNIQUE NOT NULL COMMENT '6801010101 (อัตโนมัติ)',
    group_name VARCHAR(100) NOT NULL COMMENT 'เช่น ชสส. / สสส. / สสค.',
    entry_year INT NOT NULL COMMENT 'ปีที่เข้าเรียน เช่น 2567',
    level VARCHAR(50) NOT NULL COMMENT 'ปวช. / ปวส. / ปริญญาตรี',
    subject_type_id INT,
    major_id INT,
    group_number INT NOT NULL COMMENT 'กลุ่มเรียน เช่น 01, 02',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (subject_type_id) REFERENCES subject_types(id) ON DELETE SET NULL,
    FOREIGN KEY (major_id) REFERENCES majors(id) ON DELETE SET NULL
);

-- ============================================
-- 7. ข้อมูลครู-อาจารย์ (Teachers)
-- ============================================
CREATE TABLE IF NOT EXISTS teachers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    teacher_code VARCHAR(20) UNIQUE NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    gender ENUM('ชาย', 'หญิง', 'อื่นๆ') DEFAULT 'ชาย',
    major_id INT COMMENT 'สาขาวิชา',
    username VARCHAR(50) UNIQUE,
    password VARCHAR(255),
    email VARCHAR(100),
    phone VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (major_id) REFERENCES majors(id) ON DELETE SET NULL
);

-- ============================================
-- 8. ข้อมูลนักเรียน (Students)
-- ============================================
CREATE TABLE IF NOT EXISTS students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_code VARCHAR(20) UNIQUE NOT NULL COMMENT '68010101001 (อัตโนมัติ)',
    full_name VARCHAR(100) NOT NULL,
    gender ENUM('ชาย', 'หญิง', 'อื่นๆ') DEFAULT 'ชาย',
    birthdate DATE,
    class_group_id INT COMMENT 'กลุ่มเรียน',
    username VARCHAR(50) UNIQUE,
    password VARCHAR(255),
    email VARCHAR(100),
    phone VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (class_group_id) REFERENCES class_groups(id) ON DELETE SET NULL
);

-- ============================================
-- 9. ข้อมูลห้องเรียน (Classrooms)
-- ============================================
CREATE TABLE IF NOT EXISTS classrooms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    room_code VARCHAR(20) UNIQUE NOT NULL COMMENT 'เช่น 1061, 1062, 241, 242',
    room_name VARCHAR(100) NOT NULL COMMENT 'เช่น ห้องปฏิบัติการเทคโนโลยีสารสนเทศ 242',
    building VARCHAR(50) COMMENT 'อาคาร',
    floor INT COMMENT 'ชั้น',
    capacity INT DEFAULT 30,
    room_type VARCHAR(50) COMMENT 'ห้องเรียน / ห้องปฏิบัติการ',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================
-- 10. ข้อมูลรายวิชา (Subjects)
-- ============================================
CREATE TABLE IF NOT EXISTS subjects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    subject_code VARCHAR(20) UNIQUE NOT NULL,
    subject_name VARCHAR(100) NOT NULL,
    credits INT DEFAULT 1,
    major_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (major_id) REFERENCES majors(id) ON DELETE SET NULL
);

-- ============================================
-- 11. Teacher-Subject assignments
-- ============================================
CREATE TABLE IF NOT EXISTS teacher_subjects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    teacher_id INT NOT NULL,
    subject_id INT NOT NULL,
    FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
    UNIQUE KEY unique_teacher_subject (teacher_id, subject_id)
);

-- ============================================
-- 12. Student-Subject enrollments
-- ============================================
CREATE TABLE IF NOT EXISTS student_subjects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    subject_id INT NOT NULL,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
    UNIQUE KEY unique_student_subject (student_id, subject_id)
);

-- ============================================
-- 13. ข้อมูลตารางเรียนอัตโนมัติ (Timetable)
-- ============================================
CREATE TABLE IF NOT EXISTS timetable (
    id INT AUTO_INCREMENT PRIMARY KEY,
    day_id INT NOT NULL,
    time_slot_id INT NOT NULL,
    teacher_id INT NOT NULL,
    subject_id INT NOT NULL,
    classroom_id INT NOT NULL,
    class_group_id INT COMMENT 'กลุ่มเรียน',
    semester VARCHAR(20) COMMENT 'ภาคเรียน',
    academic_year VARCHAR(20) COMMENT 'ปีการศึกษา',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (day_id) REFERENCES days(id) ON DELETE CASCADE,
    FOREIGN KEY (time_slot_id) REFERENCES time_slots(id) ON DELETE CASCADE,
    FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
    FOREIGN KEY (classroom_id) REFERENCES classrooms(id) ON DELETE CASCADE,
    FOREIGN KEY (class_group_id) REFERENCES class_groups(id) ON DELETE SET NULL,
    UNIQUE KEY unique_teacher_time (teacher_id, day_id, time_slot_id),
    UNIQUE KEY unique_classroom_time (classroom_id, day_id, time_slot_id),
    UNIQUE KEY unique_group_subject_time (class_group_id, subject_id, day_id, time_slot_id)
);

-- ============================================
-- Insert Default Data
-- ============================================

-- Insert default admin user (password: admin123)
INSERT INTO users (username, password, full_name, role) VALUES 
('admin', '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcfl7p92ldGxad68LJZdL17lhWy', 'System Administrator', 'admin')
ON DUPLICATE KEY UPDATE username=username;

-- Insert days
INSERT INTO days (day_code, day_name, day_order) VALUES
('MON', 'วันจันทร์', 1),
('TUE', 'วันอังคาร', 2),
('WED', 'วันพุธ', 3),
('THU', 'วันพฤหัสบดี', 4),
('FRI', 'วันศุกร์', 5),
('SAT', 'วันเสาร์', 6),
('SUN', 'วันอาทิตย์', 7)
ON DUPLICATE KEY UPDATE day_code=day_code;

-- Insert time slots
INSERT INTO time_slots (time_code, time_range, time_order, notes) VALUES
('T01', '08.00-09.00', 1, NULL),
('T02', '09.00-10.00', 2, NULL),
('T03', '10.00-11.00', 3, NULL),
('T04', '11.00-12.00', 4, NULL),
('T05', '12.00-13.00', 5, 'พักรับประทานอาหารกลางวัน'),
('T06', '13.00-14.00', 6, NULL),
('T07', '14.00-15.00', 7, NULL),
('T08', '15.00-16.00', 8, NULL)
ON DUPLICATE KEY UPDATE time_code=time_code;

-- Insert sample subject types
INSERT INTO subject_types (type_code, type_name) VALUES
('01', 'ประเภทวิชาบริหารธุรกิจ'),
('09', 'ประเภทวิชาอุตสาหกรรมดิจิทัลและเทคโนโลยีสารสนเทศ')
ON DUPLICATE KEY UPDATE type_code=type_code;

-- Insert sample majors
INSERT INTO majors (major_code, major_name, subject_type_id) VALUES
('01', 'การบัญชี', 1),
('02', 'การตลาด', 1),
('22', 'เทคโนโลยีสารสนเทศ', 2)
ON DUPLICATE KEY UPDATE major_code=major_code;


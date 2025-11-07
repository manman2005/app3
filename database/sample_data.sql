-- Sample data for Timetable Management System
USE timetable_db;

-- Clear existing data (optional)
-- DELETE FROM student_subjects;
-- DELETE FROM teacher_subjects;
-- DELETE FROM timetable;
-- DELETE FROM students;
-- DELETE FROM teachers;
-- DELETE FROM subjects;
-- DELETE FROM classrooms;

-- Teachers
INSERT INTO teachers (teacher_code, full_name, email, phone) VALUES
('T001', 'ครูสมชาย ใจดี', 'somchai@example.com', '0811111111'),
('T002', 'ครูสมหญิง สายใจ', 'somying@example.com', '0822222222'),
('T003', 'ครูวีระชัย อุดม', 'weerachai@example.com', '0833333333'),
('T004', 'ครูศิริพร สกุลดี', 'siriporn@example.com', '0844444444'),
('T005', 'ครูภานุทัต ศรีไทย', 'panutat@example.com', '0855555555'),
('T006', 'ครูนิภาพร วันดี', 'nipaporn@example.com', '0866666666'),
('T007', 'ครูชัยวัฒน์ วิริยะ', 'chaiwat@example.com', '0877777777'),
('T008', 'ครูอารีย์ ภูมิใจ', 'aree@example.com', '0888888888'),
('T009', 'ครูมนัส สุขสันต์', 'manus@example.com', '0899999999'),
('T010', 'ครูปรียา มหานิยม', 'preeya@example.com', '0801234567');

-- Students
INSERT INTO students (student_code, full_name, email, phone, grade) VALUES
('S001', 'กิตติพงษ์ แสงทอง', 's001@example.com', '0901111111', 'ปวช1/1'),
('S002', 'จิราพร นาคไทย', 's002@example.com', '0901111112', 'ปวช1/1'),
('S003', 'ธีรภัทร อนุรักษ์', 's003@example.com', '0901111113', 'ปวช1/2'),
('S004', 'นฤมล สาระดี', 's004@example.com', '0901111114', 'ปวช1/2'),
('S005', 'พงศกร ศรีชัย', 's005@example.com', '0901111115', 'ปวช2/1'),
('S006', 'ศุภนิดา แก้วใส', 's006@example.com', '0901111116', 'ปวช2/1'),
('S007', 'อภิสิทธิ์ สุวรรณ', 's007@example.com', '0901111117', 'ปวช2/2'),
('S008', 'กาญจนา รุ่งเรือง', 's008@example.com', '0901111118', 'ปวช2/2'),
('S009', 'ธนกฤต นามบุญ', 's009@example.com', '0901111119', 'ปวช3/1'),
('S010', 'ลลิตา สุนทร', 's010@example.com', '0901111120', 'ปวช3/1');

-- Subjects
INSERT INTO subjects (subject_code, subject_name, credits) VALUES
('SUB001', 'คณิตศาสตร์พื้นฐาน', 1),
('SUB002', 'วิทยาศาสตร์ทั่วไป', 1),
('SUB003', 'ภาษาไทย', 1),
('SUB004', 'ภาษาอังกฤษ', 1),
('SUB005', 'สังคมศึกษา', 1),
('SUB006', 'ประวัติศาสตร์', 1),
('SUB007', 'คอมพิวเตอร์', 1),
('SUB008', 'ศิลปะ', 1),
('SUB009', 'ดนตรี', 1),
('SUB010', 'พลศึกษา', 1);

-- Classrooms
INSERT INTO classrooms (room_code, room_name, capacity, room_type) VALUES
('R101', 'ห้องเรียน R101', 40, 'ห้องเรียน'),
('R102', 'ห้องเรียน R102', 40, 'ห้องเรียน'),
('R103', 'ห้องเรียน R103', 40, 'ห้องเรียน'),
('R201', 'ห้องวิทยาศาสตร์ R201', 35, 'ห้องปฏิบัติการ'),
('R202', 'ห้องคอมพิวเตอร์ R202', 30, 'ห้องปฏิบัติการ'),
('R203', 'ห้องศิลปะ R203', 35, 'ห้องศิลปะ'),
('R204', 'ห้องดนตรี R204', 35, 'ห้องดนตรี'),
('R205', 'ห้องพลศึกษา R205', 50, 'ห้องกีฬา'),
('R301', 'ห้องโสต R301', 60, 'ห้องเอนกประสงค์'),
('R302', 'ห้องประชุม R302', 80, 'ห้องประชุม');

-- Teacher-Subject assignments
INSERT INTO teacher_subjects (teacher_id, subject_id)
SELECT t.id, s.id FROM teachers t, subjects s WHERE t.teacher_code = 'T001' AND s.subject_code = 'SUB001';
INSERT INTO teacher_subjects (teacher_id, subject_id)
SELECT t.id, s.id FROM teachers t, subjects s WHERE t.teacher_code = 'T002' AND s.subject_code = 'SUB002';
INSERT INTO teacher_subjects (teacher_id, subject_id)
SELECT t.id, s.id FROM teachers t, subjects s WHERE t.teacher_code = 'T003' AND s.subject_code = 'SUB003';
INSERT INTO teacher_subjects (teacher_id, subject_id)
SELECT t.id, s.id FROM teachers t, subjects s WHERE t.teacher_code = 'T004' AND s.subject_code = 'SUB004';
INSERT INTO teacher_subjects (teacher_id, subject_id)
SELECT t.id, s.id FROM teachers t, subjects s WHERE t.teacher_code = 'T005' AND s.subject_code = 'SUB005';
INSERT INTO teacher_subjects (teacher_id, subject_id)
SELECT t.id, s.id FROM teachers t, subjects s WHERE t.teacher_code = 'T006' AND s.subject_code = 'SUB006';
INSERT INTO teacher_subjects (teacher_id, subject_id)
SELECT t.id, s.id FROM teachers t, subjects s WHERE t.teacher_code = 'T007' AND s.subject_code = 'SUB007';
INSERT INTO teacher_subjects (teacher_id, subject_id)
SELECT t.id, s.id FROM teachers t, subjects s WHERE t.teacher_code = 'T008' AND s.subject_code = 'SUB008';
INSERT INTO teacher_subjects (teacher_id, subject_id)
SELECT t.id, s.id FROM teachers t, subjects s WHERE t.teacher_code = 'T009' AND s.subject_code = 'SUB009';
INSERT INTO teacher_subjects (teacher_id, subject_id)
SELECT t.id, s.id FROM teachers t, subjects s WHERE t.teacher_code = 'T010' AND s.subject_code = 'SUB010';

-- Student-Subject enrollments
INSERT INTO student_subjects (student_id, subject_id)
SELECT st.id, s.id FROM students st, subjects s WHERE st.student_code = 'S001' AND s.subject_code = 'SUB001';
INSERT INTO student_subjects (student_id, subject_id)
SELECT st.id, s.id FROM students st, subjects s WHERE st.student_code = 'S002' AND s.subject_code = 'SUB002';
INSERT INTO student_subjects (student_id, subject_id)
SELECT st.id, s.id FROM students st, subjects s WHERE st.student_code = 'S003' AND s.subject_code = 'SUB003';
INSERT INTO student_subjects (student_id, subject_id)
SELECT st.id, s.id FROM students st, subjects s WHERE st.student_code = 'S004' AND s.subject_code = 'SUB004';
INSERT INTO student_subjects (student_id, subject_id)
SELECT st.id, s.id FROM students st, subjects s WHERE st.student_code = 'S005' AND s.subject_code = 'SUB005';
INSERT INTO student_subjects (student_id, subject_id)
SELECT st.id, s.id FROM students st, subjects s WHERE st.student_code = 'S006' AND s.subject_code = 'SUB006';
INSERT INTO student_subjects (student_id, subject_id)
SELECT st.id, s.id FROM students st, subjects s WHERE st.student_code = 'S007' AND s.subject_code = 'SUB007';
INSERT INTO student_subjects (student_id, subject_id)
SELECT st.id, s.id FROM students st, subjects s WHERE st.student_code = 'S008' AND s.subject_code = 'SUB008';
INSERT INTO student_subjects (student_id, subject_id)
SELECT st.id, s.id FROM students st, subjects s WHERE st.student_code = 'S009' AND s.subject_code = 'SUB009';
INSERT INTO student_subjects (student_id, subject_id)
SELECT st.id, s.id FROM students st, subjects s WHERE st.student_code = 'S010' AND s.subject_code = 'SUB010';

-- Timetable sample entries
INSERT INTO timetable (day_of_week, period, teacher_id, subject_id, classroom_id, student_group)
SELECT 1, 1, t.id, s.id, c.id, 'ปวช1/1'
FROM teachers t, subjects s, classrooms c
WHERE t.teacher_code = 'T001' AND s.subject_code = 'SUB001' AND c.room_code = 'R101';

INSERT INTO timetable (day_of_week, period, teacher_id, subject_id, classroom_id, student_group)
SELECT 1, 2, t.id, s.id, c.id, 'ปวช1/1'
FROM teachers t, subjects s, classrooms c
WHERE t.teacher_code = 'T002' AND s.subject_code = 'SUB002' AND c.room_code = 'R101';

INSERT INTO timetable (day_of_week, period, teacher_id, subject_id, classroom_id, student_group)
SELECT 1, 3, t.id, s.id, c.id, 'ปวช1/2'
FROM teachers t, subjects s, classrooms c
WHERE t.teacher_code = 'T003' AND s.subject_code = 'SUB003' AND c.room_code = 'R102';

INSERT INTO timetable (day_of_week, period, teacher_id, subject_id, classroom_id, student_group)
SELECT 1, 4, t.id, s.id, c.id, 'ปวช1/2'
FROM teachers t, subjects s, classrooms c
WHERE t.teacher_code = 'T004' AND s.subject_code = 'SUB004' AND c.room_code = 'R102';

INSERT INTO timetable (day_of_week, period, teacher_id, subject_id, classroom_id, student_group)
SELECT 2, 1, t.id, s.id, c.id, 'ปวช2/1'
FROM teachers t, subjects s, classrooms c
WHERE t.teacher_code = 'T005' AND s.subject_code = 'SUB005' AND c.room_code = 'R103';

INSERT INTO timetable (day_of_week, period, teacher_id, subject_id, classroom_id, student_group)
SELECT 2, 2, t.id, s.id, c.id, 'ปวช2/1'
FROM teachers t, subjects s, classrooms c
WHERE t.teacher_code = 'T006' AND s.subject_code = 'SUB006' AND c.room_code = 'R103';

INSERT INTO timetable (day_of_week, period, teacher_id, subject_id, classroom_id, student_group)
SELECT 2, 3, t.id, s.id, c.id, 'ปวช2/2'
FROM teachers t, subjects s, classrooms c
WHERE t.teacher_code = 'T007' AND s.subject_code = 'SUB007' AND c.room_code = 'R202';

INSERT INTO timetable (day_of_week, period, teacher_id, subject_id, classroom_id, student_group)
SELECT 2, 4, t.id, s.id, c.id, 'ปวช2/2'
FROM teachers t, subjects s, classrooms c
WHERE t.teacher_code = 'T008' AND s.subject_code = 'SUB008' AND c.room_code = 'R203';

INSERT INTO timetable (day_of_week, period, teacher_id, subject_id, classroom_id, student_group)
SELECT 3, 1, t.id, s.id, c.id, 'ปวช3/1'
FROM teachers t, subjects s, classrooms c
WHERE t.teacher_code = 'T009' AND s.subject_code = 'SUB009' AND c.room_code = 'R204';

INSERT INTO timetable (day_of_week, period, teacher_id, subject_id, classroom_id, student_group)
SELECT 3, 2, t.id, s.id, c.id, 'ปวช3/1'
FROM teachers t, subjects s, classrooms c
WHERE t.teacher_code = 'T010' AND s.subject_code = 'SUB010' AND c.room_code = 'R205';



<?php

    include '../Config/Config.php';
    class CDC
    {
        private $conn;

        public function __construct($db)
        {
            $this->conn = $db;
        }

        /* GET ADMIN DETAILS DATA */
        public function getAdminDetails($id)
        {
            $stmt = $this->conn->prepare("SELECT * FROM admins WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            return $stmt->get_result()->fetch_assoc();
        }

        public function updateAdmin($id, $first_name, $last_name, $username, $password = null, $profile_pic = null)
        {
            if ($password && $profile_pic)
            {
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                $sql = "UPDATE admins SET first_name=?, last_name=?, username=?, password=?, profile_pic=? WHERE id=?";
                $stmt = $this->conn->prepare($sql);
                $stmt->bind_param("sssssi", $first_name, $last_name, $username, $hashed, $profile_pic, $id);
            }
            
            elseif ($password)
            {
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                $sql = "UPDATE admins SET first_name=?, last_name=?, username=?, password=? WHERE id=?";
                $stmt = $this->conn->prepare($sql);
                $stmt->bind_param("ssssi", $first_name, $last_name, $username, $hashed, $id);
            }
            
            elseif ($profile_pic)
            {
                $sql = "UPDATE admins SET first_name=?, last_name=?, username=?, profile_pic=? WHERE id=?";
                $stmt = $this->conn->prepare($sql);
                $stmt->bind_param("ssssi", $first_name, $last_name, $username, $profile_pic, $id);
            }
            
            else
            {
                $sql = "UPDATE admins SET first_name=?, last_name=?, username=? WHERE id=?";
                $stmt = $this->conn->prepare($sql);
                $stmt->bind_param("sssi", $first_name, $last_name, $username, $id);
            }

            return $stmt->execute();
        }

        /* GET VERIFIED STUDENTS */
        public function getStudentDetails($student_id)
        {
            $sql = "SELECT vs.student_id, vs.id_num, vs.first_name, vs.last_name, vs.email, vs.contact_num, vs.course, vc.name AS company_name FROM verified_students vs LEFT JOIN verified_companies vc ON vs.company_id = vc.company_id WHERE vs.student_id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("i", $student_id);
            $stmt->execute();
            $result = $stmt->get_result();
            return $result->fetch_assoc();
        }

        /* GET VERIFIED STUDENTS DATA AND SEARCH FUNCTION */
        public function getStudents($search = "")
        {
            $search = trim($search);

            $baseSql = "SELECT s.*, c.name AS company_name, se.performance_score, se.evaluation_rank FROM verified_students s LEFT JOIN verified_companies c 
                            ON s.company_id = c.company_id
                        LEFT JOIN student_evaluations se 
                            ON se.student_id = s.student_id
                            AND se.date_issued = (
                                SELECT MAX(date_issued) 
                                FROM student_evaluations 
                                WHERE student_id = s.student_id
                            )";

            if ($search !== "")
            {
                $like = "%" . $search . "%";

                $stmt = $this->conn->prepare(
                    $baseSql . " 
                    WHERE s.id_num LIKE ? 
                    OR s.first_name LIKE ? 
                    OR s.last_name LIKE ? 
                    OR s.email LIKE ?
                    ORDER BY s.student_id DESC"
                );

                if (!$stmt)
                {
                    return $this->conn->query($baseSql . " ORDER BY s.student_id DESC");
                }

                $stmt->bind_param("ssss", $like, $like, $like, $like);
                $stmt->execute();

                if (method_exists($stmt, 'get_result'))
                {
                    return $stmt->get_result();
                }
                
                else
                {
                    $likeEsc = $this->conn->real_escape_string($like);
                    $sql = $baseSql . " 
                        WHERE s.id_num LIKE '$likeEsc' 
                        OR s.first_name LIKE '$likeEsc' 
                        OR s.last_name LIKE '$likeEsc' 
                        OR s.email LIKE '$likeEsc'
                        ORDER BY s.student_id DESC";
                    return $this->conn->query($sql);
                }
            }

            $sql = $baseSql . " ORDER BY s.student_id DESC";
            return $this->conn->query($sql);
        }

        /* GET UNVERIFIED STUDENTS DATA */
        public function getUnverifiedStudents($search = "")
        {
            if (!empty($search))
            {
                $like = "%" . $search . "%";
                $stmt = $this->conn->prepare("SELECT * FROM unverified_students WHERE id_num LIKE ? OR first_name LIKE ? OR last_name LIKE ? OR email LIKE ? ORDER BY student_id DESC");
                $stmt->bind_param("ssss", $like, $like, $like, $like);
                $stmt->execute();
                return $stmt->get_result();
            }

            else
            {
                $sql = "SELECT * FROM unverified_students ORDER BY student_id DESC";
                return $this->conn->query($sql);
            }
        }

        /* GET UNVERIFIED STUDENTS DATA FOR VERIFICATION */
        public function getStudentById($id)
        {
            $sql = "SELECT * FROM unverified_students WHERE student_id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            return $stmt->get_result()->fetch_assoc();
        }

        /* VERIFY UNVERIFIED STUDENTS FUNCTION */
        public function verifyStudent($id)
        {
            $stmt = $this->conn->prepare("SELECT * FROM unverified_students WHERE student_id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 0)
            {
                return false;
            }

            $student = $result->fetch_assoc();

            $this->conn->begin_transaction();

            $insert = $this->conn->prepare("INSERT INTO verified_students (id_num, first_name, last_name, course, email, contact_num, password, cor) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $insert->bind_param("ssssssss",
                $student['id_num'],
                $student['first_name'],
                $student['last_name'],
                $student['course'],
                $student['email'],
                $student['contact_num'],
                $student['password'],
                $student['cor']
            );

            if (!$insert->execute())
            {
                $this->conn->rollback();
                return false;
            }

            $newStudentId = $this->conn->insert_id;

            $required = $this->getRequiredHoursByCourse($student['course']);
            $ojtInsert = $this->conn->prepare("INSERT INTO ojt_progress (student_id, done_hours, required_hours) VALUES (?, 0, ?)");
            $ojtInsert->bind_param("ii", $newStudentId, $required);

            if (!$ojtInsert->execute())
            {
                $this->conn->rollback();
                return false;
            }

            $delete = $this->conn->prepare("DELETE FROM unverified_students WHERE student_id = ?");
            $delete->bind_param("i", $id);

            if (!$delete->execute())
            {
                $this->conn->rollback();
                return false;
            }

            $this->conn->commit();
            return true;
        }

        /* DELETE UNVERIFIED STUDENTS FUNCTION */
        function deleteUnverifiedStudent($id)
        {
            $sql = "DELETE FROM unverified_students WHERE student_id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("i", $id);
            return $stmt->execute();
        }

        /* GET VERIFIED STUDENTS COUNT PER COURSE FOR PIE CHART */
        public function getStudentsByCourse()
        {
            $sql = "SELECT 
                    SUM(CASE WHEN course = 'Information Technology' THEN 1 ELSE 0 END) AS IT,
                    SUM(CASE WHEN course = 'Computer Science' THEN 1 ELSE 0 END) AS CS,
                    SUM(CASE WHEN course = 'Entertainment and Multimedia' THEN 1 ELSE 0 END) AS EMC
                    FROM verified_students";

            $result = $this->conn->query($sql);
            return $result->fetch_assoc();
        }

        /* GET UNVERIFIED STUDENTS AND COMPANIES COUNT FOR PIE CHART */
        public function getUnverifiedCounts()
        {
            $sqlStudents = "SELECT COUNT(*) AS total FROM unverified_students";
            $sqlCompanies = "SELECT COUNT(*) AS total FROM unverified_companies";

            $students = $this->conn->query($sqlStudents)->fetch_assoc()['total'];
            $companies = $this->conn->query($sqlCompanies)->fetch_assoc()['total'];

            return [
                'students' => $students,
                'companies' => $companies
            ];
        }

        /* GET NUMBER OF VERIFIED STUDENTS */
        public function getVerifiedStudentsNum()
        {
            $result = $this->conn->query("SELECT COUNT(*) AS count FROM verified_students");
            return $result->fetch_assoc()['count'];
        }

        /* GET NUMBER OF UNVERIFIED STUDENTS */
        public function getUnverifiedStudentsNum()
        {
            $result = $this->conn->query("SELECT COUNT(*) AS count FROM unverified_students");
            return $result->fetch_assoc()['count'];
        }

        /* GET VERIFIED STUDENTS ID FOR UPDATE */
        public function getVerifiedStudentById($id)
        {
            $stmt = $this->conn->prepare("SELECT * FROM verified_students WHERE student_id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            return $stmt->get_result()->fetch_assoc();
        }

        /* UPDATE VERIFIED STUDENTS FUNCTION */
        public function updateVerifiedStudent($id, $first_name, $last_name, $id_num, $email, $contact_num, $course, $password)
        {
            $check = $this->conn->prepare("SELECT student_id FROM verified_students WHERE id_num = ? AND student_id != ?");
            $check->bind_param("si", $id_num, $id);
            $check->execute();
            $check->store_result();

            if ($check->num_rows > 0)
            {
                return "duplicate";
            }
            
            $stmt = $this->conn->prepare("UPDATE verified_students SET first_name = ?, last_name = ?, id_num = ?, email = ?, contact_num = ?, course = ?, password = ? WHERE student_id = ?");
            $stmt->bind_param("sssssssi", $first_name, $last_name, $id_num, $email, $contact_num, $course, $password, $id);
            
            if (!$stmt->execute())
            {
                return false;
            }

            $required = $this->getRequiredHoursByCourse($course);

            $chk = $this->conn->prepare("SELECT done_hours FROM ojt_progress WHERE student_id = ?");
            $chk->bind_param("i", $id);
            $chk->execute();
            $res = $chk->get_result();

            if ($res && $res->num_rows > 0)
            {
                $row = $res->fetch_assoc();
                $done = (int)$row['done_hours'];

                if ($done > $required) $done = $required;

                $upd = $this->conn->prepare("UPDATE ojt_progress SET required_hours = ?, done_hours = ? WHERE student_id = ?");
                $upd->bind_param("iii", $required, $done, $id);
                $upd->execute();
            }
            
            else
            {
                $ins = $this->conn->prepare("INSERT INTO ojt_progress (student_id, done_hours, required_hours) VALUES (?, 0, ?)");
                $ins->bind_param("ii", $id, $required);
                $ins->execute();
            }   

            return true;
        }

        /* DELETE VERIFIED STUDENTS FUNCTION */
        public function deleteVerifiedStudent($id)
        {
            $stmt = $this->conn->prepare("DELETE FROM verified_students WHERE student_id = ?");
            $stmt->bind_param("i", $id);
            return $stmt->execute();
        }

        /* GET VERIFIED COMPANIES DETAILS */
        public function getCompanyDetails($company_id)
        {
            $sql = "SELECT * FROM verified_companies WHERE company_id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("i", $company_id);
            $stmt->execute();
            $result = $stmt->get_result();
            return $result->fetch_assoc();
        }

        /* GET VERIFIED COMPANIES DATA */
        public function getCompanies()
        {
            $sql = "SELECT c.company_id, c.name, c.username, c.password, c.email, c.contact_num, c.address, c.website, 
                    COALESCE(COUNT(DISTINCT s.student_id), 0) AS num_students,
                    COALESCE(COUNT(DISTINCT j.job_id), 0) AS num_job_postings FROM verified_companies c
                    LEFT JOIN verified_students s ON c.company_id = s.company_id
                    LEFT JOIN job_opportunities j ON c.company_id = j.company_id
                    GROUP BY c.company_id, c.name, c.username, c.password, c.email, c.contact_num, c.address, c.website";

            return $this->conn->query($sql);
        }

        /* GET VERIFIED COMPANIES DATA FOR TABLE AND SEARCH FUNCTION */
        public function searchCompanies($search = "")
        {
            $search = trim($search);

            $baseSql = "SELECT c.company_id, c.name, c.username, c.password, c.email, c.contact_num, c.address, c.website,
                        COALESCE(COUNT(DISTINCT s.student_id), 0) AS num_students,
                        COALESCE(COUNT(DISTINCT j.job_id), 0) AS num_job_postings
                        FROM verified_companies c
                        LEFT JOIN verified_students s ON c.company_id = s.company_id
                        LEFT JOIN job_opportunities j ON c.company_id = j.company_id";

            if ($search !== "")
            {
                $like = "%" . $search . "%";

                if (is_numeric($search))
                {
                    $stmt = $this->conn->prepare(
                        $baseSql . " 
                        WHERE c.company_id = ? 
                        OR c.name LIKE ?
                        OR c.username LIKE ?
                        OR c.email LIKE ?
                        OR c.address LIKE ?
                        OR c.website LIKE ?
                        GROUP BY c.company_id, c.name, c.username, c.password, c.email, c.contact_num, c.address, c.website
                        ORDER BY c.company_id DESC"
                    );

                    if (!$stmt)
                    {
                        return $this->conn->query($baseSql . " 
                            GROUP BY c.company_id, c.name, c.username, c.password, c.email, c.contact_num, c.address, c.website
                            ORDER BY c.company_id DESC");
                    }

                    $stmt->bind_param("isssss", $search, $like, $like, $like, $like, $like);
                }
                
                else
                {
                    $stmt = $this->conn->prepare(
                        $baseSql . " 
                        WHERE c.name LIKE ?
                        OR c.username LIKE ?
                        OR c.email LIKE ?
                        OR c.address LIKE ?
                        OR c.website LIKE ?
                        GROUP BY c.company_id, c.name, c.username, c.password, c.email, c.contact_num, c.address, c.website
                        ORDER BY c.company_id DESC"
                    );

                    if (!$stmt)
                    {
                        return $this->conn->query($baseSql . " 
                            GROUP BY c.company_id, c.name, c.username, c.password, c.email, c.contact_num, c.address, c.website
                            ORDER BY c.company_id DESC");
                    }

                    $stmt->bind_param("sssss", $like, $like, $like, $like, $like);
                }

                $stmt->execute();
                return $stmt->get_result();
            }

            $sql = $baseSql . " 
                    GROUP BY c.company_id, c.name, c.username, c.password, c.email, c.contact_num, c.address, c.website
                    ORDER BY c.company_id DESC";
            return $this->conn->query($sql);
        }

        /* GET UNVERIFIED COMPANIES DATA */
        public function getUnverifiedCompanies($search = "")
        {
            if (!empty($search))
            {
                $like = "%" . $search . "%";
                $stmt = $this->conn->prepare("SELECT * FROM unverified_companies WHERE name LIKE ? OR username LIKE ? OR email LIKE ? OR contact_num LIKE ? ORDER BY company_id DESC");
                $stmt->bind_param("ssss", $like, $like, $like, $like);
                $stmt->execute();
                return $stmt->get_result();
            }
            
            else
            {
                $sql = "SELECT * FROM unverified_companies ORDER BY company_id DESC";
                return $this->conn->query($sql);
            }
        }

        /* GET UNVERIFIED COMPANIES DATA FOR VERIFICATION */
        public function getCompanyById($id) 
        {
            $stmt = $this->conn->prepare("SELECT * FROM unverified_companies WHERE company_id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            return $stmt->get_result()->fetch_assoc();
        }

        /* VERIFY UNVERIFIED COMPANIES FUNCTION */
        public function verifyCompany($id)
        {
            $stmt = $this->conn->prepare("SELECT * FROM unverified_companies WHERE company_id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 0)
            {
                return false;
            }

            $company = $result->fetch_assoc();

            $insert = $this->conn->prepare("INSERT INTO verified_companies (name, username, email, contact_num, address, website, password, id_img, moa) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $insert->bind_param(
                "sssssssss",
                $company['name'],
                $company['username'],
                $company['email'],
                $company['contact_num'],
                $company['address'],
                $company['website'],
                $company['password'],
                $company['id_img'],
                $company['moa']
            );

            if (!$insert->execute())
            {
                return false;
            }

            $delete = $this->conn->prepare("DELETE FROM unverified_companies WHERE company_id = ?");
            $delete->bind_param("i", $id);
            $delete->execute();

            return true;
        }

        /* DELETE UNVERIFIED COMPANIES FUNCTION */
        function deleteUnverifiedCompany($id)
        {
            $sql = "DELETE FROM unverified_companies WHERE company_id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("i", $id);
            return $stmt->execute();
        }

        /* GET NUMBER OF VERIFIED COMPANIES */
        public function getVerifiedCompaniesNum()
        {
            $result = $this->conn->query("SELECT COUNT(*) AS count FROM verified_companies");
            return $result->fetch_assoc()['count'];
        }

        /* GET NUMBER OF UNVERIFIED COMPANIES */
        public function getUnverifiedCompaniesNum()
        {
            $result = $this->conn->query("SELECT COUNT(*) AS count FROM unverified_companies");
            return $result->fetch_assoc()['count'];
        }

        /* GET VERIFIED COMPANIES ID FOR UPDATE */
        public function getVerifiedCompanyById($id)
        {
            $stmt = $this->conn->prepare("SELECT * FROM verified_companies WHERE company_id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            return $stmt->get_result()->fetch_assoc();
        }

        /* UPDATE VERIFIED COMPANIES FUNCTION */
        public function updateVerifiedCompany($id, $name, $username, $email, $contact_num, $address, $website, $password)
        {
            $stmt = $this->conn->prepare("UPDATE verified_companies SET name = ?, username = ?, email = ?, contact_num = ?, address = ?, website = ?, password = ? WHERE company_id = ?");
            $stmt->bind_param("sssssssi", $name, $username, $email, $contact_num, $address, $website, $password, $id);
            return $stmt->execute();
        }

        /* DELETE VERIFIED COMPANIES FUNCTION */
        public function deleteVerifiedCompany($id)
        {
            $stmt = $this->conn->prepare("DELETE FROM verified_companies WHERE company_id = ?");
            $stmt->bind_param("i", $id);
            return $stmt->execute();
        }

        /* ASSIGNING OF COMPANY TO STUDENT FUNCTION */
        public function assignCompanyToStudent($student_id, $company_id)
        {
            if (is_null($company_id))
            {
                $stmt = $this->conn->prepare("UPDATE verified_students SET company_id = NULL WHERE student_id = ?");
                if (!$stmt) return false;
                $stmt->bind_param("i", $student_id);
            }
            else
            {
                $stmt = $this->conn->prepare("UPDATE verified_students SET company_id = ? WHERE student_id = ?");
                if (!$stmt) return false;
                $stmt->bind_param("ii", $company_id, $student_id);
            }

            return $stmt->execute();
        }

        /* GET STUDENTS OJT PROGRESS DATA AND SEARCH FUNCTION */
        public function getStudentsWithOJT($search = "")
        {
            $search = trim($search);

            $baseSql = "SELECT s.student_id, s.first_name, s.last_name, s.id_num, s.course,
                            o.done_hours, o.required_hours
                        FROM verified_students s
                        LEFT JOIN ojt_progress o ON s.student_id = o.student_id";

            if ($search !== "")
            {
                $like = "%" . $search . "%";
                $stmt = $this->conn->prepare(
                    $baseSql . " 
                    WHERE s.id_num LIKE ?
                    OR s.first_name LIKE ?
                    OR s.last_name LIKE ?
                    OR s.course LIKE ?
                    ORDER BY s.student_id DESC"
                );

                $stmt->bind_param("ssss", $like, $like, $like, $like);
                $stmt->execute();
                return $stmt->get_result();
            }

            $sql = $baseSql . " ORDER BY s.student_id DESC";
            return $this->conn->query($sql);
        }

        /* GET STUDENTS SPECIFIC DETAILS, OJT PROGRESS DATA, COMPANY */
        public function getStudentOJTById($id)
        {
            $sql = "SELECT s.student_id, s.first_name, s.last_name, s.id_num, s.course, c.name AS company,
                        o.done_hours, o.required_hours
                    FROM verified_students s
                    LEFT JOIN verified_companies c ON s.company_id = c.company_id
                    LEFT JOIN ojt_progress o ON s.student_id = o.student_id
                    WHERE s.student_id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            return $stmt->get_result()->fetch_assoc();
        }

        /* ENSURES THE OJT RECORD */
        public function ensureOJTRecord($student_id, $course)
        {
            $stmt = $this->conn->prepare("SELECT ojtprog_id FROM ojt_progress WHERE student_id = ?");
            $stmt->bind_param("i", $student_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows == 0)
            {
                $required = $this->getRequiredHoursByCourse($course);
                $done = 0;

                $insert = $this->conn->prepare("INSERT INTO ojt_progress (student_id, done_hours, required_hours) VALUES (?, ?, ?)");
                $insert->bind_param("iii", $student_id, $done, $required);
                $insert->execute();
            }
        }

        /* GETS THE REQUIRED HOURS PER COURSE FUNCTION */
        private function getRequiredHoursByCourse($course)
        {
            $c = strtolower(trim($course));
            if (strpos($c, 'information') !== false || $c === 'it' || $c === 'Information Technology')
            {
                return 500;
            }
            if (strpos($c, 'computer') !== false || $c === 'cs' || $c === 'Computer Science')
            {
                return 300;
            }
            if (strpos($c, 'entertainment') !== false || strpos($c, 'Entertainment and Multimedia') !== false || $c === 'emc')
            {
                return 250;
            }

            return 500;
        }

        /* UPDATE OJT HOURS FUNCTION */
        public function updateOJTProgress($student_id, $done_hours)
        {
            $stmt = $this->conn->prepare("UPDATE ojt_progress SET done_hours = ? WHERE student_id = ?");
            $stmt->bind_param("ii", $done_hours, $student_id);
            return $stmt->execute();
        }

        /* SEND NOTIFICATIONS TO VERIFIED STUDENTS FUNCTION */
        public function sendNotification($id_num, $title, $description, $date_sent = null, $deadline = null)
        {
            $stmt = $this->conn->prepare("SELECT student_id FROM verified_students WHERE id_num = ?");
            $stmt->bind_param("s", $id_num);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 0)
            {
                return "student_not_found";
            }

            $student = $result->fetch_assoc();
            $student_id = $student['student_id'];

            if ($date_sent && $deadline)
            {
                $insert = $this->conn->prepare("INSERT INTO notifications (student_id, title, description, date_sent, deadline) VALUES (?, ?, ?, ?, ?)");
                $insert->bind_param("issss", $student_id, $title, $description, $date_sent, $deadline);
            }
            
            elseif ($date_sent)
            {
                $insert = $this->conn->prepare("INSERT INTO notifications (student_id, title, description, date_sent) VALUES (?, ?, ?, ?)");
                $insert->bind_param("isss", $student_id, $title, $description, $date_sent);
            }
            
            elseif ($deadline)
            {
                $insert = $this->conn->prepare("INSERT INTO notifications (student_id, title, description, deadline) VALUES (?, ?, ?, ?)");
                $insert->bind_param("isss", $student_id, $title, $description, $deadline);
            }
            
            else
            {
                $insert = $this->conn->prepare("INSERT INTO notifications (student_id, title, description) VALUES (?, ?, ?)");
                $insert->bind_param("iss", $student_id, $title, $description);
            }

            return $insert->execute();
        }

        /* GET SUBMISSION REPORTS DATA AND SEARCH FUNCTION */
        public function getSubmissionReports($search = "")
        {
            $baseSql = "SELECT sr.report_id, sr.report_file, sr.date_sent,
                            vs.student_id, vs.first_name, vs.last_name, vs.id_num, vs.course
                        FROM submission_reports sr
                        INNER JOIN verified_students vs ON sr.student_id = vs.student_id";

            if (!empty($search))
            {
                $like = "%" . $this->conn->real_escape_string($search) . "%";
                $baseSql .= " WHERE vs.first_name LIKE '$like'
                            OR vs.last_name LIKE '$like'
                            OR vs.id_num LIKE '$like'
                            OR vs.course LIKE '$like'
                            OR sr.report_file LIKE '$like'";
            }

            $baseSql .= " ORDER BY sr.date_sent DESC";

            return $this->conn->query($baseSql);
        }

        /* GET JOB OPPORTUNITIES DATA AND SEARCH FUNCTION */
        public function getJobOpportunities($search = "")
        {
            $baseSql = "SELECT j.*, c.name AS company_name
                        FROM job_opportunities j
                        JOIN verified_companies c ON j.company_id = c.company_id";

            $search = trim($search);
            if ($search !== "") {
                $stmt = $this->conn->prepare(
                    $baseSql . " WHERE j.job_title LIKE ? 
                                OR j.job_location LIKE ? 
                                OR j.job_tags LIKE ? 
                                OR c.name LIKE ?
                                ORDER BY j.date_sent DESC"
                );
                $like = "%{$search}%";
                $stmt->bind_param("ssss", $like, $like, $like, $like);
                $stmt->execute();
                return $stmt->get_result();
            }

            $sql = $baseSql . " ORDER BY j.date_sent DESC";
            return $this->conn->query($sql);
        }

        /* GET JOB OPPORTUNITIES DATA BY ID */
        public function getJobById($job_id)
        {
            $sql = "SELECT j.*, c.name FROM job_opportunities j INNER JOIN verified_companies c ON j.company_id = c.company_id WHERE j.job_id = ?";
                    
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("i", $job_id);
            $stmt->execute();
            $result = $stmt->get_result();

            return $result->fetch_assoc();
        }

        /* DELETE JOB OPPORTUNITIES FUNCTION */
        public function deleteJobOpportunity($job_id)
        {
            $stmt = $this->conn->prepare("DELETE FROM job_opportunities WHERE job_id = ?");
            $stmt->bind_param("i", $job_id);
            return $stmt->execute();
        }

        /* GET NUMBER OF JOB OPPORTUNITIES */
        public function getJobOppNum()
        {
            $result = $this->conn->query("SELECT COUNT(*) AS count FROM job_opportunities");
            return $result->fetch_assoc()['count'];
        }

        /* GET AVERAGE EVALUATION RANKS PER COURSE FUNCTION */
        public function getAvgByCourse($course)
        {
            global $conn;

            $sql = "SELECT AVG(evaluation_rank) as avg_rank FROM student_evaluations se JOIN verified_students s ON se.student_id = s.student_id WHERE s.course = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $course);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();

            if (!$result['avg_rank'])
            {
                return "<span class='avg gray'>N/A</span>";
            }

            $avg = round($result['avg_rank'], 2);

            $label = "";
            $class = "";

            if ($avg >= 75)
            {
                $class = "good";
                $label = "Good";
            }

            elseif ($avg >= 51)
            {
                $class = "medium";
                $label = "Fair";
            }
            
            elseif ($avg > 0)
            {
                $class = "bad";
                $label = "Bad";
            }
            
            else
            {
                $class = "gray";
            }

            return "<div class='avg {$class}'>{$avg}<br><small>{$label}</small></div>";
        }
    }
?>

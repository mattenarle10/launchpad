<?php

    include '../Config/Config.php';

    class PC
    {
        private $conn;

        public function __construct($conn)
        {
            $this->conn = $conn;
        }

        /* SIGN UP */
        public function registerCompany($username, $name, $website, $email, $contact_num, $address, $password, $moaPath, $idPath)
        {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $this->conn->prepare("INSERT INTO unverified_companies (username, name, website, email, contact_num, address, moa, id_img, password) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssssss", $username, $name, $website, $email, $contact_num, $address, $moaPath, $idPath, $hashedPassword);

            return $stmt->execute();
        }

        /* GET COMPANY DETAILS */
        public function getCompanyDetails($company_id)
        {
            $stmt = $this->conn->prepare("SELECT * FROM verified_companies WHERE company_id = ?");
            $stmt->bind_param("i", $company_id);
            $stmt->execute();
            return $stmt->get_result()->fetch_assoc();
        }

        /* EDIT PROFILE */
        public function editProfile($id, $name, $username, $email, $contact_num, $address, $password = null, $moa = null, $company_id_img = null, $profile_pic = null)
        {
            if ($password && $moa && $company_id_img && $profile_pic)
            {
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                $sql = "UPDATE verified_companies SET name=?, username=?, email=?, contact_num=?, address=?, password=?, moa=?, id_img=?, profile_pic=? WHERE company_id=?";
                $stmt = $this->conn->prepare($sql);
                $stmt->bind_param("sssssssssi", $name, $username, $email, $contact_num, $address, $hashed, $moa, $company_id_img, $profile_pic, $id);
            }

            elseif ($password && $moa && $company_id_img)
            {
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                $sql = "UPDATE verified_companies SET name=?, username=?, email=?, contact_num=?, address=?, password=?, moa=?, id_img=? WHERE company_id=?";
                $stmt = $this->conn->prepare($sql);
                $stmt->bind_param("ssssssssi", $name, $username, $email, $contact_num, $address, $hashed, $moa, $company_id_img, $id);
            }

            elseif ($password && $moa && $profile_pic)
            {
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                $sql = "UPDATE verified_companies SET name=?, username=?, email=?, contact_num=?, address=?, password=?, moa=?, profile_pic=? WHERE company_id=?";
                $stmt = $this->conn->prepare($sql);
                $stmt->bind_param("ssssssssi", $name, $username, $email, $contact_num, $address, $hashed, $moa, $profile_pic, $id);
            }

            elseif ($password && $company_id_img && $profile_pic)
            {
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                $sql = "UPDATE verified_companies SET name=?, username=?, email=?, contact_num=?, address=?, password=?, id_img=?, profile_pic=? WHERE company_id=?";
                $stmt = $this->conn->prepare($sql);
                $stmt->bind_param("ssssssssi", $name, $username, $email, $contact_num, $address, $hashed, $company_id_img, $profile_pic, $id);
            }

            elseif ($password && $moa)
            {
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                $sql = "UPDATE verified_companies SET name=?, username=?, email=?, contact_num=?, address=?, password=?, moa=? WHERE company_id=?";
                $stmt = $this->conn->prepare($sql);
                $stmt->bind_param("sssssssi", $name, $username, $email, $contact_num, $address, $hashed, $moa, $id);
            }

            elseif ($password && $company_id_img)
            {
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                $sql = "UPDATE verified_companies SET name=?, username=?, email=?, contact_num=?, address=?, password=?, id_img=? WHERE company_id=?";
                $stmt = $this->conn->prepare($sql);
                $stmt->bind_param("sssssssi", $name, $username, $email, $contact_num, $address, $hashed, $company_id_img, $id);
            }

            elseif ($password && $profile_pic)
            {
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                $sql = "UPDATE verified_companies SET name=?, username=?, email=?, contact_num=?, address=?, password=?, profile_pic=? WHERE company_id=?";
                $stmt = $this->conn->prepare($sql);
                $stmt->bind_param("sssssssi", $name, $username, $email, $contact_num, $address, $hashed, $profile_pic, $id);
            }

            elseif ($moa && $company_id_img && $profile_pic)
            {
                $sql = "UPDATE verified_companies SET name=?, username=?, email=?, contact_num=?, address=?, moa=?, id_img=?, profile_pic=? WHERE company_id=?";
                $stmt = $this->conn->prepare($sql);
                $stmt->bind_param("sssssssii", $name, $username, $email, $contact_num, $address, $moa, $company_id_img, $profile_pic, $id);
            }

            elseif ($moa && $company_id_img)
            {
                $sql = "UPDATE verified_companies SET name=?, username=?, email=?, contact_num=?, address=?, moa=?, id_img=? WHERE company_id=?";
                $stmt = $this->conn->prepare($sql);
                $stmt->bind_param("sssssssi", $name, $username, $email, $contact_num, $address, $moa, $company_id_img, $id);
            }

            elseif ($moa && $profile_pic)
            {
                $sql = "UPDATE verified_companies SET name=?, username=?, email=?, contact_num=?, address=?, moa=?, profile_pic=? WHERE company_id=?";
                $stmt = $this->conn->prepare($sql);
                $stmt->bind_param("sssssssi", $name, $username, $email, $contact_num, $address, $moa, $profile_pic, $id);
            }

            elseif ($company_id_img && $profile_pic)
            {
                $sql = "UPDATE verified_companies SET name=?, username=?, email=?, contact_num=?, address=?, id_img=?, profile_pic=? WHERE company_id=?";
                $stmt = $this->conn->prepare($sql);
                $stmt->bind_param("sssssssi", $name, $username, $email, $contact_num, $address, $company_id_img, $profile_pic, $id);
            }

            elseif ($password)
            {
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                $sql = "UPDATE verified_companies SET name=?, username=?, email=?, contact_num=?, address=?, password=? WHERE company_id=?";
                $stmt = $this->conn->prepare($sql);
                $stmt->bind_param("ssssssi", $name, $username, $email, $contact_num, $address, $hashed, $id);
            }

            elseif ($moa)
            {
                $sql = "UPDATE verified_companies SET name=?, username=?, email=?, contact_num=?, address=?, moa=? WHERE company_id=?";
                $stmt = $this->conn->prepare($sql);
                $stmt->bind_param("ssssssi", $name, $username, $email, $contact_num, $address, $moa, $id);
            }

            elseif ($company_id_img)
            {
                $sql = "UPDATE verified_companies SET name=?, username=?, email=?, contact_num=?, address=?, id_img=? WHERE company_id=?";
                $stmt = $this->conn->prepare($sql);
                $stmt->bind_param("ssssssi", $name, $username, $email, $contact_num, $address, $company_id_img, $id);
            }

            elseif ($profile_pic)
            {
                $sql = "UPDATE verified_companies SET name=?, username=?, email=?, contact_num=?, address=?, profile_pic=? WHERE company_id=?";
                $stmt = $this->conn->prepare($sql);
                $stmt->bind_param("ssssssi", $name, $username, $email, $contact_num, $address, $profile_pic, $id);
            }

            else
            {
                $sql = "UPDATE verified_companies SET name=?, username=?, email=?, contact_num=?, address=? WHERE company_id=?";
                $stmt = $this->conn->prepare($sql);
                $stmt->bind_param("sssssi", $name, $username, $email, $contact_num, $address, $id);
            }

            return $stmt->execute();
        }

        /* GET STUDENTS OF COMPANY */
        public function getStudentsByCompany($company_id, $search = "")
        {
            $search = "%" . $search . "%";

            $sql = "SELECT s.student_id, s.first_name, s.last_name, s.course, s.email, s.contact_num,
                        e.evaluation_rank, e.performance_score
                    FROM verified_students s
                    LEFT JOIN student_evaluations e 
                        ON s.student_id = e.student_id AND e.company_id = ?
                    WHERE s.company_id = ?
                    AND (
                        s.first_name LIKE ? 
                        OR s.last_name LIKE ? 
                        OR s.course LIKE ? 
                        OR s.email LIKE ?
                    )
                    ORDER BY s.last_name ASC";

            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("iissss", $company_id, $company_id, $search, $search, $search, $search);
            $stmt->execute();
            return $stmt->get_result();
        }

        /* GET STUDENT DETAILS */
        public function getStudentDetails($company_id, $student_id)
        {
            $sql = "SELECT s.student_id, s.first_name, s.last_name, s.course, s.email, s.contact_num,
                        e.evaluation_rank, e.performance_score
                    FROM verified_students s
                    LEFT JOIN student_evaluations e 
                        ON s.student_id = e.student_id AND e.company_id = ?
                    WHERE s.student_id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("ii", $company_id, $student_id);
            $stmt->execute();
            return $stmt->get_result()->fetch_assoc();
        }

        /* EVALUATE STUDENTS FUNCTION */
        public function saveEvaluation($company_id, $student_id, $eval_rank)
        {
            $check = $this->conn->prepare("SELECT eval_id FROM student_evaluations WHERE company_id=? AND student_id=?");
            $check->bind_param("ii", $company_id, $student_id);
            $check->execute();
            $res = $check->get_result();

            if ($res->num_rows > 0)
            {
                $sql = "UPDATE student_evaluations SET evaluation_rank=? WHERE company_id=? AND student_id=?";
                $stmt = $this->conn->prepare($sql);
                $stmt->bind_param("iii", $eval_rank, $company_id, $student_id);
            } 
            
            else 
            {
                $sql = "INSERT INTO student_evaluations (company_id, student_id, evaluation_rank) VALUES (?, ?, ?)";
                $stmt = $this->conn->prepare($sql);
                $stmt->bind_param("iii", $company_id, $student_id, $eval_rank);
            }

            return $stmt->execute();
        }

        /* CREATE JOB OPPORTUNITY */
        public function createJobOpportunity($company_id, $title, $location, $setup, $tags, $pay_min, $pay_max, $requirements, $responsibilities)
        {
            $sql = "INSERT INTO job_opportunities (company_id, job_title, job_location, job_setup, job_tags, job_pay_min, job_pay_max, job_requirements, job_responsibilities) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("issssssss", $company_id, $title, $location, $setup, $tags, $pay_min, $pay_max, $requirements, $responsibilities);
            return $stmt->execute();
        }

        /* GET JOB OPPOTUNITIES AND SEARCH FUNCTION */
        public function searchCompanyJobs($company_id, $search = "")
        {
            $searchTerm = "%" . $search . "%";
            $sql = "SELECT * FROM job_opportunities WHERE company_id = ? AND (job_title LIKE ? 
                        OR job_location LIKE ? 
                        OR job_tags LIKE ? 
                        OR job_requirements LIKE ? 
                        OR job_responsibilities LIKE ?) 
                    ORDER BY date_sent DESC";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("isssss", $company_id, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm);
            $stmt->execute();
            return $stmt->get_result();
        }

        /* DELETE JOB OPPORTUNITY */
        public function deleteJob($job_id, $company_id)
        {
            $sql = "DELETE FROM job_opportunities WHERE job_id = ? AND company_id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("ii", $job_id, $company_id);
            return $stmt->execute();
        }

        /* GET JOB OPPORTUNITY BY ID */
        public function getJobById($job_id, $company_id)
        {
            $sql = "SELECT * FROM job_opportunities WHERE job_id = ? AND company_id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("ii", $job_id, $company_id);
            $stmt->execute();
            return $stmt->get_result()->fetch_assoc();
        }

        /* EDIT JOB OPPORTUNITY */
        public function updateJob($job_id, $company_id, $job_title, $job_location, $job_setup, $job_tags, $job_pay_min, $job_pay_max, $job_requirements, $job_responsibilities)
        {
            $sql = "UPDATE job_opportunities SET job_title=?, job_location=?, job_setup=?, job_tags=?, job_pay_min=?, job_pay_max=?, job_requirements=?, job_responsibilities=? WHERE job_id=? AND company_id=?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("ssssssssii", $job_title, $job_location, $job_setup, $job_tags, $job_pay_min, $job_pay_max, $job_requirements, $job_responsibilities, $job_id, $company_id);
            return $stmt->execute();
        }

        /* GET STUDENTS PER COURSE */
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

        /* GET TOTAL NUMBER OF STUDENTS OF THE COMPANY */
        public function getTotalCompanyStudents($company_id)
        {
            $sql = "SELECT COUNT(*) AS total_students FROM verified_students WHERE company_id = ?";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("i", $company_id);
            $stmt->execute();
            $result = $stmt->get_result();

            return $result->fetch_assoc()['total_students'] ?? 0;
        }

        /* GET JOB OPPORTUNITIES DATA */
        public function getTotalJobOpp($company_id)
        {
            $sql = "SELECT COUNT(*) AS total_jobs FROM job_opportunities WHERE company_id = ?";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("i", $company_id);
            $stmt->execute();
            $result = $stmt->get_result();

            return $result->fetch_assoc()['total_jobs'] ?? 0;
        }

        /* GET THE TOP 3 RANKED STUDENTS */
        public function getTopRankedStudents($company_id, $limit = 3)
        {
            $sql = "SELECT vs.first_name, vs.last_name, se.evaluation_rank
                    FROM student_evaluations se
                    INNER JOIN verified_students vs ON se.student_id = vs.student_id
                    WHERE se.company_id = ?
                    ORDER BY se.evaluation_rank DESC
                    LIMIT ?";

            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("ii", $company_id, $limit);
            $stmt->execute();
            $result = $stmt->get_result();

            $students = [];

            while ($row = $result->fetch_assoc())
            {
                $students[] = $row;
            }

            return $students;
        }
        
    }

?>

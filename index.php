<?php
session_start();
include("db.php"); 

// Establish database connection
$con = mysqli_connect("localhost", "root", "", "medical"); 
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

// Check if form is submitted
if (isset($_POST['submit'])) {
    // Retrieve form data
    $patientName = isset($_POST['inputPatientName']) ? trim($_POST['inputPatientName']) : '';
    $doctorName = isset($_POST['inputDoctorName']) ? trim($_POST['inputDoctorName']) : '';
    $departmentName = isset($_POST['inputDepartmentName']) ? trim($_POST['inputDepartmentName']) : '';
    $phoneNumber = isset($_POST['inputPhone']) ? trim($_POST['inputPhone']) : '';
    $symptoms = isset($_POST['inputSymptoms']) ? trim($_POST['inputSymptoms']) : '';
    $appointmentDate = isset($_POST['inputDate']) ? trim($_POST['inputDate']) : '';

    // Clean up phone number
    $phoneNumber = preg_replace('/\D/', '', $phoneNumber);

    // Validate form inputs
    if (empty($patientName) || empty($doctorName) || empty($departmentName) || empty($phoneNumber) || empty($symptoms) || empty($appointmentDate)) {
        die("All fields are required.");
    }

    // Validate phone number length
    if (strlen($phoneNumber) != 11) {
        die("Invalid phone number format.");
    }

    // Handle file upload
    $uploadDirectory = "uploads/";
    $uploadedFilePath = "upload"; // Initialize the variable
    if (isset($_FILES['inputFile']) && $_FILES['inputFile']['error'] == UPLOAD_ERR_OK) {
        $allowedFileTypes = ['application/pdf', 'image/jpeg', 'image/png']; // Allowed file types
        if (in_array($_FILES['inputFile']['type'], $allowedFileTypes)) {
            $uploadedFileName = basename($_FILES['inputFile']['name']);
            $uploadedFilePath = $uploadDirectory . $uploadedFileName; // Correct path assignment
            if (!is_dir($uploadDirectory)) {
                mkdir($uploadDirectory, 0755, true);
            }
            if (move_uploaded_file($_FILES['inputFile']['tmp_name'], $uploadedFilePath)) {
                // File successfully uploaded
                echo "File uploaded successfully.";
            } else {
                echo "File upload failed, please try again.";
            }
        } else {
            echo "Invalid file type. Only PDF, JPEG, and PNG are allowed.";
        }
    } else {
        echo "No file was uploaded.";
    }

    // Prepare file data to be stored in the database
    $fileData = file_get_contents($uploadedFilePath);
    $fileData = mysqli_real_escape_string($con, $fileData); // Escape special characters

    // Generate digital signature
    $dataToSign = $patientName . $doctorName . $departmentName . $phoneNumber . $symptoms . $appointmentDate;
    $signature = hash('sha256', $dataToSign);

    // Insert data into database along with the signature
    $que = "INSERT INTO `appointments` (`PatientName`, `DoctorName`, `DepartmentName`, `PhoneNumber`, `Symptoms`, `AppointmentDate`, `Signature`, `FileData`) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = mysqli_prepare($con, $que);
    mysqli_stmt_bind_param($stmt, "ssssssss", $patientName, $doctorName, $departmentName, $phoneNumber, $symptoms, $appointmentDate, $signature, $fileData);
    mysqli_stmt_execute($stmt);

    // Close statement
    mysqli_stmt_close($stmt);

    // Redirect to main page after submission
    header('Location: about.php');
    exit; 
}
?>

<?php
// Signature verification code
include("db.php"); 

// Establish database connection
$con = mysqli_connect("localhost", "root", "", "medical"); 
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

// Retrieve data from the database
$appointmentId = isset($_GET['id']) ? $_GET['id'] : null; // Assuming you have appointment ID in the URL
$query = "SELECT * FROM `appointments` WHERE `AppointmentId` = ?";
$stmt = mysqli_prepare($con, $query);
mysqli_stmt_bind_param($stmt, "i", $appointmentId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$appointment = mysqli_fetch_assoc($result);

// Close statement
mysqli_stmt_close($stmt);

// Verify signature
if ($appointment) {
    // Recreate the signature
    $dataToSign = $appointment['PatientName'] . $appointment['DoctorName'] . $appointment['DepartmentName'] . $appointment['PhoneNumber'] . $appointment['Symptoms'] . $appointment['AppointmentDate'];
    $recreatedSignature = hash('sha256', $dataToSign);

    // Compare signatures
    if ($recreatedSignature === $appointment['Signature']) {
        echo "Signature verified. Data integrity maintained.";
    } else {
        echo "Signature mismatch. Data may have been tampered with.";
    }
} else {
    echo "Appointment not found.";
}
?>




<!DOCTYPE html>
<html>

<head>
  <!-- Basic -->
  <meta charset="utf-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <!-- Mobile Metas -->
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
  <!-- Site Metas -->
  <meta name="keywords" content="" />
  <meta name="description" content="" />
  <meta name="author" content="" />

  <title>Medical</title>


  <!-- bootstrap core css -->
  <link rel="stylesheet" type="text/css" href="css/bootstrap.css" />

  <!-- fonts style -->
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700;900&display=swap" rel="stylesheet">

  <!--owl slider stylesheet -->
  <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css" />

  <!-- font awesome style -->
  <link href="css/font-awesome.min.css" rel="stylesheet" />
  <!-- nice select -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-nice-select/1.1.0/css/nice-select.min.css" integrity="sha256-mLBIhmBvigTFWPSCtvdu6a76T+3Xyt+K571hupeFLg4=" crossorigin="anonymous" />
  <!-- datepicker -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.3.0/css/datepicker.css">
  <!-- Custom styles for this template -->
  <link href="css/style.css" rel="stylesheet" />
  <!-- responsive style -->
  <link href="css/responsive.css" rel="stylesheet" />

</head>

<body>

  <div class="hero_area">
    <!-- header section strats -->
    <header class="header_section">
      <div class="header_top">
        <div class="container">
          <div class="contact_nav">
            <a href="">
              <i class="fa fa-phone" aria-hidden="true"></i>
                <span>
                Call : 09099778434
              </span>
            </a>
            <a href="">
              <i class="fa fa-envelope" aria-hidden="true"></i>
              <span>
                Email : medical@gmail.com
              </span>
            </a>
            <a href="">
              <i class="fa fa-map-marker" aria-hidden="true"></i>
              <span>
                Boac Marinduque
              </span>
            </a>
          </div>
        </div>
      </div>
      <div class="header_bottom">
        <div class="container-fluid">
          <nav class="navbar navbar-expand-lg custom_nav-container ">
            <a class="navbar-brand" href="index.php">
              <img src="images/logo.png" alt="">
            </a>


            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
              <span class=""> </span>
            </button>

            <div class="collapse navbar-collapse" id="navbarSupportedContent">
              <div class="d-flex mr-auto flex-column flex-lg-row align-items-center">
                <ul class="navbar-nav  ">
                  <li class="nav-item active">
                    <a class="nav-link" href="index.php">Home <span class="sr-only">(current)</span></a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" href="about.php"> About</a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" href="treatment.php">Treatment</a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" href="doctor.php">Doctors</a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" href="testimonial.php">Testimonial</a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" href="contact.php">Contact Us</a>
                  </li>
                </ul>
              </div>
              <div class="quote_btn-container">
                <a href="login.php">
                  <i class="fa fa-user" aria-hidden="true"></i>
                  <span>
                    Login
                  </span>
                </a>
                <a href="signup.php">
                  <i class="fa fa-user" aria-hidden="true"></i>
                  <span>
                    Sign Up
                  </span>
                </a>
                <form class="form-inline">
                  <button class="btn  my-2 my-sm-0 nav_search-btn" type="submit">
                    <i class="fa fa-search" aria-hidden="true"></i>
                  </button>
                </form>
              </div>
            </div>
          </nav>
        </div>
      </div>
    </header>
    <!-- end header section -->
    <!-- slider section -->
    <section class="slider_section ">
      <div class="dot_design">
        <img src="images/dots.png" alt="">
      </div>
      <div id="customCarousel1" class="carousel slide" data-ride="carousel">
        <div class="carousel-inner">
          <div class="carousel-item active">
            <div class="container ">
              <div class="row">
                <div class="col-md-6">
                  <div class="detail-box">
                    <div class="play_btn">
                      <button>
                        <i class="fa fa-play" aria-hidden="true"></i>
                      </button>
                    </div>
                    <h1>
                      New Hope General <br>
                      <span>
                        Hospital
                      </span>
                    </h1>
                    <p>
                      New Hope General Hospital is a leading healthcare facility dedicated to providing high-quality medical services to our community. With a team of experienced doctors, nurses, and staff, we strive to deliver compassionate care and exceptional outcomes to our patients.
                    </p>
                    <a href="">
                      Contact Us
                    </a>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="img-box">
                    <img src="images/slider-img.jpg" alt="">
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="carousel-item">
            <div class="container ">
              <div class="row">
                <div class="col-md-6">
                  <div class="detail-box">
                    <div class="play_btn">
                      <button>
                        <i class="fa fa-play" aria-hidden="true"></i>
                      </button>
                    </div>
                    <h1>
                      New Hope General <br>
                      <span>
                        Hospital
                      </span>
                    </h1>
                    <p>
                      New Hope General Hospital is a leading healthcare facility dedicated to providing high-quality medical services to our community. With a team of experienced doctors, nurses, and staff, we strive to deliver compassionate care and exceptional outcomes to our patients.
                    </p>
                    <a href="">
                      Contact Us
                    </a>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="img-box">
                    <img src="images/slider-img.jpg" alt="">
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="carousel-item">
            <div class="container ">
              <div class="row">
                <div class="col-md-6">
                  <div class="detail-box">
                    <div class="play_btn">
                      <button>
                        <i class="fa fa-play" aria-hidden="true"></i>
                      </button>
                    </div>
                    <h1>
                      New Hope General <br>
                      <span>
                        Hospital
                      </span>
                    </h1>
                    <p>
                     New Hope General Hospital is a leading healthcare facility dedicated to providing high-quality medical services to our community. With a team of experienced doctors, nurses, and staff, we strive to deliver compassionate care and exceptional outcomes to our patients.
                    </p>
                    <a href="">
                      Contact Us
                    </a>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="img-box">
                    <img src="images/slider-img.jpg" alt="">
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="carousel_btn-box">
          <a class="carousel-control-prev" href="#customCarousel1" role="button" data-slide="prev">
            <img src="images/prev.png" alt="">
            <span class="sr-only">Previous</span>
          </a>
          <a class="carousel-control-next" href="#customCarousel1" role="button" data-slide="next">
            <img src="images/next.png" alt="">
            <span class="sr-only">Next</span>
          </a>
        </div>
      </div>

    </section>
    <!-- end slider section -->
  </div>


  <!-- book section -->

  <section class="book_section layout_padding">
    <div class="container">
      <form action="" method="POST" enctype="multipart/form-data">
    <div class="row">
        <div class="col">
            <h4>BOOK <span>APPOINTMENT</span></h4>
            <div class="form-row">
                <div class="form-group col-lg-4">
                    <label for="inputPatientName">Patient Name </label>
                    <input type="text" class="form-control" id="inputPatientName" name="inputPatientName" placeholder="">
                </div>
                <div class="form-group col-lg-4">
                    <label for="inputDoctorName">Doctor's Name</label>
                    <select class="form-control wide" id="inputDoctorName" name="inputDoctorName">
                        <option value="Dr. Allan Meneses">Dr. Allan Meneses</option>
                        <option value="Dr. Kim Manalo">Dr. Kim Manalo</option>
                        <option value="Dr. Jane Manay">Dr. Jane Manay</option>
                    </select>
                </div>
                <div class="form-group col-lg-4">
                    <label for="inputDepartmentName">Department's Name</label>
                    <select class="form-control wide" id="inputDepartmentName" name="inputDepartmentName">
                        <option value="Internal Medicine">Internal Medicine</option>
                        <option value="General Pediatrics">General Pediatrics</option>
                        <option value="Adolescent Medicine">Adolescent Medicine</option>
                    </select>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-lg-4">
                    <label for="inputPhone">Phone Number</label>
                    <input type="text" class="form-control" id="inputPhone" name="inputPhone" placeholder="XXXXXXXXXX">
                </div>
                <div class="form-group col-lg-4">
                    <label for="inputSymptoms">Symptoms</label>
                    <input type="text" class="form-control" id="inputSymptoms" name="inputSymptoms" placeholder="">
                </div>
                <div class="form-group col-lg-4">
                    <label for="inputDate">Choose Date</label>
                    <div class="input-group date" id="inputDate" data-date-format="mm-dd-yyyy">
                        <input type="text" class="form-control" id="inputDate" name="inputDate" readonly>
                        <span class="input-group-addon date_icon">
                            <i class="fa fa-calendar" aria-hidden="true"></i>
                        </span>
                    </div>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-lg-4">
                    <label for="inputFile">Upload Document</label>
                    <input type="file" class="form-control" id="inputFile" name="inputFile">
                </div>
            </div>
            <div class="btn-box">
                <button type="submit" name="submit" class="btn">Submit Now</button>
            </div>
        </div>
    </div>
</form>
</section>


  <!-- end book section -->


  <!-- about section -->

  <section class="about_section">
    <div class="container  ">
      <div class="row">
        <div class="col-md-6 ">
          <div class="img-box">
            <img src="images/about-img.jpg" alt="">
          </div>
        </div>
        <div class="col-md-6">
          <div class="detail-box">
            <div class="heading_container">
              <h2>
                About <span>Hospital</span>
              </h2>
            </div>
            <p>
              New Hope General is a leading medical facility dedicated to providing high-quality healthcare services to our community. With state-of-the-art equipment and a team of experienced medical professionals, we offer a wide range of medical services, Disease Management, Respiratory Care, Hospitalist Care, Chronic Disease Management, and more. Our mission is to promote health and wellness, deliver compassionate care, and improve the lives of our patients. We strive to uphold the highest standards of patient safety, satisfaction, and clinical excellence. At New Hope General Hospital, your health is our priority.
            </p>
            </p>
            <a href="">
              Read More
            </a>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- end about section -->


  <!-- treatment section -->

  <section class="treatment_section layout_padding">
    <div class="side_img">
      <img src="images/treatment-side-img.jpg" alt="">
    </div>
    <div class="container">
      <div class="heading_container heading_center">
        <h2>
          Hospital <span>Treatment</span>
        </h2>
      </div>
      <div class="row">
        <div class="col-md-6 col-lg-3">
          <div class="box ">
            <div class="img-box">
              <img src="images/t1.png" alt="">
            </div>
            <div class="detail-box">
              <h4>
                Disease Management
              </h4>
              <p>
                Treatment and management of chronic conditions such as asthma, COPD, heart disease, or gastrointestinal disorders.
              </p>
              <a href="">
                Read More
              </a>
            </div>
          </div>
        </div>
        <div class="col-md-6 col-lg-3">
          <div class="box ">
            <div class="img-box">
              <img src="images/t2.png" alt="">
            </div>
            <div class="detail-box">
              <h4>
                Hospitalist Care
              </h4>
              <p>
                Providing comprehensive medical care for hospitalized patients, coordinating treatment plans, and managing acute illnesses.
              </p>
              <a href="">
                Read More
              </a>
            </div>
          </div>
        </div>
        <div class="col-md-6 col-lg-3">
          <div class="box ">
            <div class="img-box">
              <img src="images/t3.png" alt="">
            </div>
            <div class="detail-box">
              <h4>
                Respiratory Care
              </h4>
              <p>
                Managing respiratory conditions like asthma or pneumonia in children.
              </p>
              <a href="">
                Read More
              </a>
            </div>
          </div>
        </div>
        <div class="col-md-6 col-lg-3">
          <div class="box ">
            <div class="img-box">
              <img src="images/t4.png" alt="">
            </div>
            <div class="detail-box">
              <h4>
                Chronic Disease Management
              </h4>
              <p>
                Managing chronic conditions like diabetes or asthma that may persist from childhood into adolescence.
              </p>
              <a href="">
                Read More
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- end treatment section -->

  <!-- team section -->

  <section class="team_section layout_padding">
    <div class="container">
      <div class="heading_container heading_center">
        <h2>
          Our <span>Doctors</span>
        </h2>
      </div>
      <div class="carousel-wrap ">
        <div class="owl-carousel team_carousel">
          <div class="item">
            <div class="box">
              <div class="img-box">
                <img src="images/team1.jpg" alt="" />
              </div>
              <div class="detail-box">
                <h5>
                  Allan Meneses
                </h5>
                <h6>
                  MBBS
                </h6>
                <div class="social_box">
                  <a href="">
                    <i class="fa fa-facebook" aria-hidden="true"></i>
                  </a>
                  <a href="">
                    <i class="fa fa-twitter" aria-hidden="true"></i>
                  </a>
                  <a href="">
                    <i class="fa fa-linkedin" aria-hidden="true"></i>
                  </a>
                  <a href="">
                    <i class="fa fa-instagram" aria-hidden="true"></i>
                  </a>
                </div>
              </div>
            </div>
          </div>
          <div class="item">
            <div class="box">
              <div class="img-box">
                <img src="images/team2.jpg" alt="" />
              </div>
              <div class="detail-box">
                <h5>
                  Jane Manay
                </h5>
                <h6>
                  MBBS
                </h6>
                <div class="social_box">
                  <a href="">
                    <i class="fa fa-facebook" aria-hidden="true"></i>
                  </a>
                  <a href="">
                    <i class="fa fa-twitter" aria-hidden="true"></i>
                  </a>
                  <a href="">
                    <i class="fa fa-linkedin" aria-hidden="true"></i>
                  </a>
                  <a href="">
                    <i class="fa fa-instagram" aria-hidden="true"></i>
                  </a>
                </div>
              </div>
            </div>
          </div>
          <div class="item">
            <div class="box">
              <div class="img-box">
                <img src="images/team3.jpg" alt="" />
              </div>
              <div class="detail-box">
                <h5>
                  Kim Manalo
                </h5>
                <h6>
                  MBBS
                </h6>
                <div class="social_box">
                  <a href="">
                    <i class="fa fa-facebook" aria-hidden="true"></i>
                  </a>
                  <a href="">
                    <i class="fa fa-twitter" aria-hidden="true"></i>
                  </a>
                  <a href="">
                    <i class="fa fa-linkedin" aria-hidden="true"></i>
                  </a>
                  <a href="">
                    <i class="fa fa-instagram" aria-hidden="true"></i>
                  </a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- end team section -->


  <!-- client section -->
  <section class="client_section layout_padding">
    <div class="container">
      <div class="heading_container">
        <h2>
          <span>Testimonial</span>
        </h2>
      </div>
    </div>
    <div class="container px-0">
      <div id="customCarousel2" class="carousel  carousel-fade" data-ride="carousel">
        <div class="carousel-inner">
          <div class="carousel-item active">
            <div class="box">
              <div class="client_info">
                <div class="client_name">
                  <h5>
                    Ynnah
                  </h5>
                  <h6>
                    Default model text
                  </h6>
                </div>
                <i class="fa fa-quote-left" aria-hidden="true"></i>
              </div>
              <p>
                My experience at New Hope General Hospital was nothing short of exceptional. From the moment I walked through the doors, I was greeted with warmth and kindness by the staff. The doctors and nurses were incredibly attentive, taking the time to listen to my concerns and explain my treatment plan in detail.
              </p>
            </div>
          </div>
          <div class="carousel-item">
            <div class="box">
              <div class="client_info">
                <div class="client_name">
                  <h5>
                    Sam
                  </h5>
                  <h6>
                    Default model text
                  </h6>
                </div>
                <i class="fa fa-quote-left" aria-hidden="true"></i>
              </div>
              <p>
                The level of care I received was outstanding. The medical team worked tirelessly to ensure that I received the best possible treatment, and I felt reassured knowing that I was in capable hands. The facilities were modern and well-equipped, and I was impressed by the hospital's commitment to maintaining a clean and safe environment.
              </p>
            </div>
          </div>
          <div class="carousel-item">
            <div class="box">
              <div class="client_info">
                <div class="client_name">
                  <h5>
                    Billy
                  </h5>
                  <h6>
                    Default model text
                  </h6>
                </div>
                <i class="fa fa-quote-left" aria-hidden="true"></i>
              </div>
              <p>
                I recently had the opportunity to visit New Hope General Hospital for a medical procedure, and I must say, my experience exceeded all expectations. From the moment I walked through the doors, I was greeted with warmth and kindness by the staff.
              </p>
            </div>
          </div>
        </div>
        <div class="carousel_btn-box">
          <a class="carousel-control-prev" href="#customCarousel2" role="button" data-slide="prev">
            <i class="fa fa-angle-left" aria-hidden="true"></i>
            <span class="sr-only">Previous</span>
          </a>
          <a class="carousel-control-next" href="#customCarousel2" role="button" data-slide="next">
            <i class="fa fa-angle-right" aria-hidden="true"></i>
            <span class="sr-only">Next</span>
          </a>
        </div>
      </div>
    </div>
  </section>
  <!-- end client section -->

  <!-- contact section -->
  <section class="contact_section layout_padding-bottom">
    <div class="container">
      <div class="heading_container">
        <h2>
          Get In Touch
        </h2>
      </div>
      <div class="row">
        <div class="col-md-7">
          <div class="form_container">
            <form action="">
              <div>
                <input type="text" placeholder="Full Name" />
              </div>
              <div>
                <input type="email" placeholder="Email" />
              </div>
              <div>
                <input type="text" placeholder="Phone Number" />
              </div>
              <div>
                <input type="text" class="message-box" placeholder="Message" />
              </div>
              <div class="btn_box">
                <button>
                  SEND
                </button>
              </div>
            </form>
          </div>
        </div>
        <div class="col-md-5">
          <div class="img-box">
            <img src="images/contact-img.jpg" alt="">
          </div>
        </div>
      </div>
    </div>
  </section>
  <!-- end contact section -->

  <!-- info section -->
  <section class="info_section ">
    <div class="container">
      <div class="info_top">
        <div class="info_logo">
          <a href="">
            <img src="images/logo.png" alt="">
          </a>
        </div>
        <div class="info_form">
          <form action="">
            <input type="email" placeholder="Your email">
            <button>
              Subscribe
            </button>
          </form>
        </div>
      </div>
      <div class="info_bottom layout_padding2">
        <div class="row info_main_row">
          <div class="col-md-6 col-lg-3">
            <h5>
              Address
            </h5>
            <div class="info_contact">
              <a href="">
                <i class="fa fa-map-marker" aria-hidden="true"></i>
                <span>
                  Boac Marinduque
                </span>
              </a>
              <a href="">
                <i class="fa fa-phone" aria-hidden="true"></i>
                <span>
                  Call 09099778434
                </span>
              </a>
              <a href="">
                <i class="fa fa-envelope"></i>
                <span>
                  medical@gmail.com
                </span>
              </a>
            </div>
            <div class="social_box">
              <a href="">
                <i class="fa fa-facebook" aria-hidden="true"></i>
              </a>
              <a href="">
                <i class="fa fa-twitter" aria-hidden="true"></i>
              </a>
              <a href="">
                <i class="fa fa-linkedin" aria-hidden="true"></i>
              </a>
              <a href="">
                <i class="fa fa-instagram" aria-hidden="true"></i>
              </a>
            </div>
          </div>
          <div class="col-md-6 col-lg-3">
            <div class="info_links">
              <h5>
                Useful link
              </h5>
              <div class="info_links_menu">
                <a class="active" href="index.php">
                  Home
                </a>
                <a href="about.php">
                  About
                </a>
                <a href="treatment.php">
                  Treatment
                </a>
                <a href="doctor.php">
                  Doctors
                </a>
                <a href="testimonial.php">
                  Testimonial
                </a>
                <a href="contact.php">
                  Contact us
                </a>
              </div>
        


  <!-- footer section -->
  <footer class="footer_section">
    <div class="container">
      <p>
        &copy; <span id="displayYear"></span> All Rights Reserved By
        <a href="https://html.design/">Medical</a>
      </p>
    </div>
  </footer>
  <!-- footer section -->

  <!-- jQery -->
  <script src="js/jquery-3.4.1.min.js"></script>
  <!-- bootstrap js -->
  <script src="js/bootstrap.js"></script>
  <!-- nice select -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-nice-select/1.1.0/js/jquery.nice-select.min.js" integrity="sha256-Zr3vByTlMGQhvMfgkQ5BtWRSKBGa2QlspKYJnkjZTmo=" crossorigin="anonymous"></script>
  <!-- owl slider -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js"></script>
  <!-- datepicker -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.3.0/js/bootstrap-datepicker.js"></script>
  <!-- custom js -->
  <script src="js/custom.js"></script>


</body>

</html>
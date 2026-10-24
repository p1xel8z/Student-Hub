<?php
session_start();

if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Register - Northbridge University</title>

    <link rel="icon" type="image/png" href="logo.png">
    <link rel="stylesheet" href="style.css">
    <script src="script.js" defer></script>
    <script src="registeration.js" defer></script>
</head>

<body>

<header>

    <div>
        <img src="logo.png" width="10%" alt="Northbridge University Logo">
        <h1>Northbridge University</h1>
    </div>

    <nav aria-label="Main navigation">

        <ul>

            <li><a href="index.html">Home</a></li>
            <li><a href="about.html">About</a></li>

            <li>
                <details>
                    <summary>Programs</summary>
                    <ul>
                        <li><a href="cse.html">B.Tech CSE</a></li>
                        <li><a href="it.html">B.Tech IT</a></li>
                        <li><a href="ce.html">B.Tech CE</a></li>
                    </ul>
                </details>
            </li>

            <li><a href="faculty.html">Faculty</a></li>

            <li>
                <details>
                    <summary>Campus Life</summary>
                    <ul>
                        <li><a href="clubs.html">Clubs &amp; Societies</a></li>
                        <li><a href="facilities.html">Facilities</a></li>
                    </ul>
                </details>
            </li>

            <li>
                <details>
                    <summary>Student Corner</summary>
                    <ul>
                        <li>
                            <a href="https://sites.google.com/view/tatkalgyan">
                                Student Resources
                            </a>
                        </li>
                        <li>
                            <a href="https://drive.google.com/drive/folders/10-XqFw6ISXnDeyajK8oB3YIIj6NfrIyO">
                                Downloads
                            </a>
                        </li>
                        <li>
                            <a href="https://drive.google.com/drive/folders/18NAT1fcOe5nG25ddphqluyIs9CLvWQ5Q">
                                Syllabus
                            </a>
                        </li>
                    </ul>
                </details>
            </li>

            <li><a href="contact.html">Contact</a></li>
            <li><a href="faq.html">FAQ</a></li>
            <li><a href="login.html">Login</a></li>

        </ul>

    </nav>

    <button id="themeToggle" type="button">Dark Mode</button>


</header>


<main>

    <section>

        <h2>Create Your Account</h2>

        <p>
            Register for a Northbridge University student account.
        </p>


    
        <form id="registrationForm" action="process.php" method="POST" novalidate>

        <input
        type="hidden"
        name="csrf_token"
        value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

          

            <fieldset>

                <legend>Personal Information</legend>


                <div class="form-group">

                    <label for="first_name">First Name</label>

                    <input
                        type="text"
                        id="first_name"
                        name="first_name"
                        autocomplete="given-name"
                        required
                        aria-describedby="firstNameError"
                    >

                    <small
                        id="firstNameError"
                        class="error-message"
                        aria-live="polite"
                    ></small>

                </div>


          
                <div class="form-group">

                    <label for="last_name">Last Name</label>

                    <input
                        type="text"
                        id="last_name"
                        name="last_name"
                        autocomplete="family-name"
                        required
                        aria-describedby="lastNameError"
                    >

                    <small
                        id="lastNameError"
                        class="error-message"
                        aria-live="polite"
                    ></small>

                </div>


              
                <div class="form-group">

                    <label for="email">Email Address</label>

                    <input
                        type="email"
                        id="email"
                        name="email"
                        autocomplete="email"
                        required
                        aria-describedby="emailError"
                    >

                    <small
                        id="emailError"
                        class="error-message"
                        aria-live="polite"
                    ></small>

                </div>


                <div class="form-group">

                    <label for="mobile">Mobile Number</label>

                    <input
                        type="tel"
                        id="mobile"
                        name="mobile"
                        inputmode="numeric"
                        pattern="[0-9]{10}"
                        maxlength="10"
                        autocomplete="tel"
                        required
                        aria-describedby="mobileError"
                    >

                    <small
                        id="mobileError"
                        class="error-message"
                        aria-live="polite"
                    ></small>

                </div>


               
                <div class="form-group">

                    <label for="date_of_birth">Date of Birth</label>

                    <input
                        type="date"
                        id="date_of_birth"
                        name="date_of_birth"
                        required
                        aria-describedby="dobError"
                    >

                    <small
                        id="dobError"
                        class="error-message"
                        aria-live="polite"
                    ></small>

                </div>


              
                <fieldset>

                    <legend>Gender</legend>

                    <input
                        type="radio"
                        id="male"
                        name="gender"
                        value="male"
                    >
                    <label for="male">Male</label>


                    <input
                        type="radio"
                        id="female"
                        name="gender"
                        value="female"
                    >
                    <label for="female">Female</label>


                    <input
                        type="radio"
                        id="other"
                        name="gender"
                        value="other"
                    >
                    <label for="other">Other</label>


                    <small
                        id="genderError"
                        class="error-message"
                        aria-live="polite"
                    ></small>

                </fieldset>

            </fieldset>


            <br>



            <fieldset>

                <legend>Academic Information</legend>


            
                <div class="form-group">

                    <label for="program">Program</label>

                    <select
                        id="program"
                        name="program"
                        required
                        aria-describedby="programError"
                    >

                        <option value="" selected disabled>
                            Select Program
                        </option>

                        <option value="cse">
                            B.Tech Computer Science and Engineering
                        </option>

                        <option value="it">
                            B.Tech Information Technology
                        </option>

                        <option value="ce">
                            B.Tech Civil Engineering
                        </option>

                    </select>

                    <small
                        id="programError"
                        class="error-message"
                        aria-live="polite"
                    ></small>

                </div>


                
                <div class="form-group">

                    <label for="year">Year of Study</label>

                    <select
                        id="year"
                        name="year"
                        required
                        aria-describedby="yearError"
                    >

                        <option value="" selected disabled>
                            Select Year
                        </option>

                        <option value="1">First Year</option>
                        <option value="2">Second Year</option>
                        <option value="3">Third Year</option>
                        <option value="4">Fourth Year</option>

                    </select>

                    <small
                        id="yearError"
                        class="error-message"
                        aria-live="polite"
                    ></small>

                </div>


            
                <div class="form-group">

                    <label for="student_id">Student ID</label>

                    <input
                        type="text"
                        id="student_id"
                        name="student_id"
                        required
                        aria-describedby="studentIdError"
                    >

                    <small
                        id="studentIdError"
                        class="error-message"
                        aria-live="polite"
                    ></small>

                </div>

            </fieldset>


            <br>


            

            <fieldset>

                <legend>Account Information</legend>


             
                <div class="form-group">

                    <label for="password">Password</label>

                    <input
                        type="password"
                        id="password"
                        name="password"
                        minlength="8"
                        autocomplete="new-password"
                        required
                        aria-describedby="passwordError passwordStrength"
                    >


                  
                    <div
                        id="passwordStrength"
                        class="password-strength"
                        aria-live="polite"
                    >
                        Password strength: —
                    </div>

                    <small
                        id="passwordError"
                        class="error-message"
                        aria-live="polite"
                    ></small>

                </div>


                
                <div class="form-group">

                    <label for="confirm_password">
                        Confirm Password
                    </label>

                    <input
                        type="password"
                        id="confirm_password"
                        name="confirm_password"
                        autocomplete="new-password"
                        required
                        aria-describedby="confirmPasswordError"
                    >

                    <small
                        id="confirmPasswordError"
                        class="error-message"
                        aria-live="polite"
                    ></small>

                </div>

            </fieldset>


            <br>


            

            <fieldset>

                <legend>Terms and Conditions</legend>

                <div class="checkbox-group">

                    <input
                        type="checkbox"
                        id="terms"
                        name="terms"
                        required
                        aria-describedby="termsError"
                    >

                    <label for="terms">
                        I agree to the Terms and Conditions.
                    </label>

                </div>

                <small
                    id="termsError"
                    class="error-message"
                    aria-live="polite"
                ></small>

            </fieldset>


            <br>


            

            <div
                id="formMessage"
                class="form-message"
                aria-live="polite"
            ></div>


            <button type="submit">
                Create Account
            </button>

        </form>


        <p>
            Already have an account?
            <a href="login.html">Login here</a>
        </p>

    </section>

</main>


<footer>

    <h2>Northbridge University</h2>

    <p>Learn. Build. Participate. Grow.</p>

    <p>&copy; 2026 Northbridge University. All rights reserved.</p>

</footer>

</body>

</html>
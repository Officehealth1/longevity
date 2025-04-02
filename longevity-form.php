<?php
// Register shortcode
function longevity_assessment_form() {
    // Ensure jQuery is loaded
    wp_enqueue_script('jquery');

    ob_start();
    ?>
    <!-- Form Container -->
    <div class="longevity-form-container">
        <!-- Google Material Icons for factor icons -->
        <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
        
        <!-- Form Sections -->
        <form id="longevityForm" class="longevity-form">
            <!-- Section 1: Personal Information -->
            <div class="form-section" id="section1">
                <h2>Personal Information</h2>
                <div class="form-group">
                    <label for="fullName">Full Name <span class="info-icon"><span class="tooltip">Please enter your full legal name as it appears on official documents.</span></span></label>
                    <input type="text" id="fullName" name="fullName" required>
                </div>
                <div class="form-group">
                    <label for="gender">Gender <span class="info-icon"><span class="tooltip">This information helps us provide more accurate health assessments and recommendations.</span></span></label>
                    <select id="gender" name="gender" required>
                        <option value="">Select Gender</option>
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="email">Email Address <span class="info-icon"><span class="tooltip">We'll send your assessment results and recommendations to this email address.</span></span></label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="age">Age <span class="info-icon"><span class="tooltip">Enter your current age in years.</span></span></label>
                    <input type="number" id="age" name="age" required min="18" max="120">
                </div>
            </div>

            <!-- Section 2: Body Measurements -->
            <div class="form-section" id="section2">
                <h2>Body Measurements</h2>
                <div class="form-group">
                    <label for="height">Height (cm) <span class="info-icon"><span class="tooltip">Measure your height in centimeters. Stand straight against a wall with your heels together.</span></span></label>
                    <input type="number" id="height" name="height" required>
                </div>
                <div class="form-group">
                    <label for="weight">Weight (kg) <span class="info-icon"><span class="tooltip">Enter your current weight in kilograms. Use a digital scale for accuracy.</span></span></label>
                    <input type="number" id="weight" name="weight" required>
                </div>
                <div class="form-group">
                    <label for="waist">Waist Circumference (cm) <span class="info-icon"><span class="tooltip">Measure around your waist at the level of your belly button. Keep the tape measure horizontal.</span></span></label>
                    <input type="number" id="waist" name="waist" required>
                </div>
                <div class="form-group">
                    <label for="hip">Hip Circumference (cm) <span class="info-icon"><span class="tooltip">Measure around the widest part of your hips. Keep the tape measure horizontal.</span></span></label>
                    <input type="number" id="hip" name="hip" required>
                </div>
            </div>

            <!-- Section 3: Lifestyle Factors -->
            <div class="form-section" id="section3">
                <h2>Lifestyle Factors</h2>
                <div class="form-group">
                    <label for="activity">Physical Activity Level <span class="info-icon"><span class="tooltip">Select the intensity level that best describes your regular physical activity. Consider both exercise and daily activities.</span></span></label>
                    <select id="activity" name="activity" required>
                        <option value="">Select Activity Level</option>
                        <option value="0">Sedentary</option>
                        <option value="1">Very low intensity</option>
                        <option value="2">Low intensity</option>
                        <option value="3" selected>Moderate intensity</option>
                        <option value="4">High intensity</option>
                        <option value="5">Very high intensity</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="sleepDuration">Sleep Duration <span class="info-icon"><span class="tooltip">Enter your average sleep duration per night. Include both deep and light sleep periods.</span></span></label>
                    <select id="sleepDuration" name="sleepDuration" required>
                        <option value="">Select Sleep Duration</option>
                        <option value="0">Less than 4 hours</option>
                        <option value="1">4 to 5 hours</option>
                        <option value="2">5 to 6 hours</option>
                        <option value="3" selected>6 to 7 hours</option>
                        <option value="4">7 to 8 hours</option>
                        <option value="5">More than 8 hours</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="sleepQuality">Sleep Quality <span class="info-icon"><span class="tooltip">Rate how well you typically sleep, considering factors like restfulness and interruptions.</span></span></label>
                    <select id="sleepQuality" name="sleepQuality" required>
                        <option value="">Select Sleep Quality</option>
                        <option value="0">Very poor</option>
                        <option value="1">Poor</option>
                        <option value="2">Below average</option>
                        <option value="3" selected>Average</option>
                        <option value="4">Good</option>
                        <option value="5">Excellent</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="stressLevels">Stress Levels <span class="info-icon"><span class="tooltip">Assess your overall stress levels, including both physical and emotional stress factors.</span></span></label>
                    <select id="stressLevels" name="stressLevels" required>
                        <option value="">Select Stress Level</option>
                        <option value="0">Extreme stress</option>
                        <option value="1">High stress</option>
                        <option value="2">Moderate stress</option>
                        <option value="3" selected>Manageable stress</option>
                        <option value="4">Low stress</option>
                        <option value="5">Minimal or no stress</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="socialConnections">Social Connections <span class="info-icon"><span class="tooltip">Evaluate your social support network and frequency of meaningful social interactions.</span></span></label>
                    <select id="socialConnections" name="socialConnections" required>
                        <option value="">Select Social Connection Level</option>
                        <option value="0">Completely isolated</option>
                        <option value="1">Very few connections</option>
                        <option value="2">Limited social interactions</option>
                        <option value="3" selected>Moderate connections</option>
                        <option value="4">Good social network</option>
                        <option value="5">Excellent social network</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="dietQuality">Diet Quality <span class="info-icon"><span class="tooltip">Rate your overall diet quality, considering variety, balance, and nutritional value.</span></span></label>
                    <select id="dietQuality" name="dietQuality" required>
                        <option value="">Select Diet Quality</option>
                        <option value="0">Very poor</option>
                        <option value="1">Poor</option>
                        <option value="2">Below average</option>
                        <option value="3" selected>Average</option>
                        <option value="4">Good</option>
                        <option value="5">Excellent</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="alcoholConsumption">Alcohol Consumption <span class="info-icon"><span class="tooltip">Select the option that best describes your typical alcohol consumption patterns.</span></span></label>
                    <select id="alcoholConsumption" name="alcoholConsumption" required>
                        <option value="">Select Alcohol Consumption</option>
                        <option value="0">Excessive daily intake</option>
                        <option value="1">Regular heavy intake</option>
                        <option value="2">Moderate regular intake</option>
                        <option value="3" selected>Light social drinking</option>
                        <option value="4">Occasional minimal intake</option>
                        <option value="5">No intake</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="smokingStatus">Smoking Status <span class="info-icon"><span class="tooltip">Indicate your current smoking status, including any history of smoking.</span></span></label>
                    <select id="smokingStatus" name="smokingStatus" required>
                        <option value="">Select Smoking Status</option>
                        <option value="0">Heavy smoker</option>
                        <option value="1">Regular smoker</option>
                        <option value="2">Occasional smoker</option>
                        <option value="3" selected>Ex-smoker</option>
                        <option value="4">Rare smoker</option>
                        <option value="5">Never smoked</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="cognitiveActivity">Cognitive Activity <span class="info-icon"><span class="tooltip">Rate your engagement in mentally stimulating activities like reading, puzzles, or learning new skills.</span></span></label>
                    <select id="cognitiveActivity" name="cognitiveActivity" required>
                        <option value="">Select Cognitive Activity Level</option>
                        <option value="0">Very low mental activity</option>
                        <option value="1">Low mental activity</option>
                        <option value="2">Below average activity</option>
                        <option value="3" selected>Moderate activity</option>
                        <option value="4">High mental activity</option>
                        <option value="5">Very high mental activity</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="sunlightExposure">Sunlight Exposure <span class="info-icon"><span class="tooltip">Consider your daily exposure to natural sunlight, including both direct and indirect exposure.</span></span></label>
                    <select id="sunlightExposure" name="sunlightExposure" required>
                        <option value="">Select Sunlight Exposure</option>
                        <option value="0">Almost no exposure</option>
                        <option value="1">Rare exposure</option>
                        <option value="2">Limited exposure</option>
                        <option value="3" selected>Moderate exposure</option>
                        <option value="4">Good exposure</option>
                        <option value="5">Optimal exposure</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="supplementIntake">Supplement Intake <span class="info-icon"><span class="tooltip">Rate your regular use of dietary supplements, vitamins, or minerals.</span></span></label>
                    <select id="supplementIntake" name="supplementIntake" required>
                        <option value="">Select Supplement Intake</option>
                        <option value="0">None</option>
                        <option value="1">Rarely</option>
                        <option value="2">Occasionally</option>
                        <option value="3" selected>Regularly</option>
                        <option value="4">Frequently</option>
                        <option value="5">Optimal supplementation</option>
                    </select>
                </div>
            </div>

            <!-- Section 4: Physical Performance -->
            <div class="form-section" id="section4">
                <h2>Physical Performance Metrics</h2>
                <div class="form-group">
                    <label for="sitStand">Sit-to-Stand Capability <span class="info-icon"><span class="tooltip">Assess your ability to perform sit-to-stand movements from a chair without assistance.</span></span></label>
                    <select id="sitStand" name="sitStand" required>
                        <option value="">Select Capability Level</option>
                        <option value="0">Unable</option>
                        <option value="1">Very difficult</option>
                        <option value="2">Some difficulty</option>
                        <option value="3" selected>Moderate capability</option>
                        <option value="4">Good capability</option>
                        <option value="5">Excellent capability</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="breathHold">Breath Hold Capacity <span class="info-icon"><span class="tooltip">Measure how long you can comfortably hold your breath after a normal inhalation.</span></span></label>
                    <select id="breathHold" name="breathHold" required>
                        <option value="">Select Breath Hold Duration</option>
                        <option value="0">Under 10 seconds</option>
                        <option value="1">10-20 seconds</option>
                        <option value="2">20-30 seconds</option>
                        <option value="3" selected>30-45 seconds</option>
                        <option value="4">45-60 seconds</option>
                        <option value="5">Over 60 seconds</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="balance">Balance Ability <span class="info-icon"><span class="tooltip">Evaluate your ability to maintain balance on one leg with eyes open.</span></span></label>
                    <select id="balance" name="balance" required>
                        <option value="">Select Balance Level</option>
                        <option value="0">Very poor</option>
                        <option value="1">Poor</option>
                        <option value="2">Below average</option>
                        <option value="3" selected>Average</option>
                        <option value="4">Good</option>
                        <option value="5">Excellent</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="skinElasticity">Skin Elasticity <span class="info-icon"><span class="tooltip">Assess your skin's ability to return to normal after being gently pinched.</span></span></label>
                    <select id="skinElasticity" name="skinElasticity" required>
                        <option value="">Select Skin Elasticity Level</option>
                        <option value="0">Very poor</option>
                        <option value="1">Poor</option>
                        <option value="2">Below average</option>
                        <option value="3" selected>Average</option>
                        <option value="4">Good</option>
                        <option value="5">Excellent</option>
                    </select>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="form-navigation">
                <button type="submit" class="nav-btn submit-btn">Calculate Results</button>
            </div>

            <!-- Results Section -->
            <div id="resultsSection" style="display: none;">
                <h2>Your Longevity Assessment Results</h2>
                <!-- Optional: Add subtitle for date here later -->
                <div class="results-container">
                    <!-- Row 1: Biological Age and Lifestyle Score -->
                    <div class="result-card" id="bioAgeCard">
                         <h3>Biological Age</h3>
                         <!-- Populated by JS: Shows Biological Age and Age Shift -->
                         <div id="biologicalAgeDisplay"></div>
                    </div>
                    <div class="result-card" id="lifestyleScoreCard">
                        <h3>Lifestyle Score</h3>
                        <!-- Populated by JS: Shows overall average score -->
                        <div id="lifestyleScore"></div> <!-- Existing content div -->
                    </div>

                    <!-- Row 2: Aging Rate -->
                    <div class="result-card full-width-card" id="agingRateCard">
                        <h3>Aging Rate</h3>
                         <!-- Populated by JS: Shows calculated aging rate -->
                        <div id="agingRateDisplay"></div>
                    </div>

                    <!-- Section: Body Measurements (Full Width) -->
                     <div class="full-width-section" id="bodyMeasurementsSection">
                         <h3>Body Composition Analysis</h3>
                         <!-- Populated by JS: Shows BMI/WHR gauges -->
                         <div id="bodyMeasurements"></div> <!-- Existing content div -->
                     </div>

                     <!-- --- Age Impact Factors Section --- -->
                     <div class="full-width-section" id="ageImpactSection">
                         <h3>Age Impact Factors</h3>
                         <div class="impact-factors-container">
                             <div class="impact-column">
                                 <div class="impact-column-header">
                                     <div class="icon impact-positive">
                                         <span class="material-icons">trending_down</span>
                                     </div>
                                     <h4>Factors Lowering Your Age</h4>
                                 </div>
                                 <!-- Positive factors will be populated by JS -->
                             </div>
                             <div class="impact-column">
                                 <div class="impact-column-header">
                                     <div class="icon impact-negative">
                                         <span class="material-icons">trending_up</span>
                                     </div>
                                     <h4>Factors Raising Your Age</h4>
                                 </div>
                                 <!-- Negative factors will be populated by JS -->
                             </div>
                         </div>
                     </div>

                     <!-- Section: Detailed Breakdown (Full Width) -->
                     <div class="full-width-section" id="detailedBreakdownSection">
                         <h3>Detailed Breakdown</h3>
                         <!-- Populated by JS: Shows scores for each metric -->
                         <div id="detailedBreakdown"></div> <!-- Existing content div -->
                     </div>

                     <!-- --- End Age Impact Factors Section --- -->
                </div>
            </div>
        </form>
    </div>

    <style>
        /* Premium Form Styling following Apple Design Guidelines */
        .longevity-form-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 3rem 2rem;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            color: #1d1d1f;
            border: 1px solid #e5e5e5;
            border-radius: 16px;
        }

        /* Form Sections */
        .form-section {
            padding: 2.5rem;
            margin-bottom: 1rem;
            border-radius: 16px;
        }

        .form-section h2 {
            color: #1d1d1f;
            margin-bottom: 2rem;
            font-size: 1.75rem;
            font-weight: 600;
            letter-spacing: -0.02em;
        }

        .form-group {
            margin-bottom: 2rem;
        }

        label {
            display: block;
            margin-bottom: 0.75rem;
            font-weight: 500;
            color: #1d1d1f;
            font-size: 1rem;
            letter-spacing: -0.01em;
        }

        input, select {
            width: 100%;
            padding: 14px 16px;
            border: 1px solid #e5e5e5;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.2s ease;
            background-color: #ffffff;
            color: #1d1d1f;
            min-height: 44px; /* Apple's minimum touch target size */
        }

        input:focus, select:focus {
            outline: none;
            border-color: #007AFF;
            box-shadow: 0 0 0 4px rgba(0,122,255,0.1);
        }

        select {
            appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 16px center;
            background-size: 16px;
            padding-right: 44px;
        }

        /* Submit Button */
        .form-navigation {
            display: flex;
            justify-content: center;
            margin-top: 3rem;
        }

        .submit-btn {
            padding: 18px 36px;
            border-radius: 12px;
            border: none;
            font-size: 1.125rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            background: #007AFF;
            color: white;
            min-width: 240px;
            min-height: 44px; /* Apple's minimum touch target size */
            letter-spacing: -0.01em;
        }

        .submit-btn:hover {
            background: #0056b3;
            transform: translateY(-1px);
        }

        .submit-btn:active {
            transform: translateY(0);
        }

        /* Results Section */
        .results-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); /* Fallback */
            grid-template-columns: repeat(2, 1fr); /* Two columns */
            gap: 2rem;
        }

        .result-card {
            padding: 2rem;
            border: 1px solid #e5e5e5;
            border-radius: 16px;
        }

        .result-card h3 {
            color: #1d1d1f;
            margin-bottom: 1.5rem;
            font-size: 1.25rem;
            font-weight: 600;
            letter-spacing: -0.02em;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .longevity-form-container {
                padding: 2rem 1rem;
            }

            .form-section {
                padding: 2rem;
            }

            .form-section h2 {
                font-size: 1.5rem;
                margin-bottom: 1.5rem;
            }

            .submit-btn {
                width: 100%;
                padding: 16px 24px;
            }

            .form-group {
                margin-bottom: 1.5rem;
            }
        }

        /* Form Validation Styles */
        input:invalid, select:invalid {
            border-color: #e5e5e5;
        }

        input:invalid:focus, select:invalid:focus {
            box-shadow: 0 0 0 4px rgba(0,122,255,0.1);
        }

        /* Loading State */
        .submit-btn.loading {
            opacity: 0.7;
            cursor: not-allowed;
        }

        /* Info Icon and Tooltip Styles */
        .form-group {
            position: relative;
        }

        .info-icon {
            display: inline-block;
            width: 16px;
            height: 16px;
            margin-left: 8px;
            vertical-align: middle;
            cursor: help;
            position: relative;
        }

        .info-icon::before {
            content: "i";
            display: flex;
            align-items: center;
            justify-content: center;
            width: 16px;
            height: 16px;
            background: #007AFF;
            color: white;
            border-radius: 50%;
            font-size: 12px;
            font-weight: bold;
        }

        .tooltip {
            visibility: hidden;
            position: absolute;
            left: 100%;
            top: 50%;
            transform: translateY(-50%);
            background: #1d1d1f;
            color: white;
            padding: 12px 16px;
            border-radius: 8px;
            font-size: 0.875rem;
            width: 240px;
            z-index: 100;
            opacity: 0;
            transition: opacity 0.2s ease;
            margin-left: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .info-icon:hover .tooltip {
            visibility: visible;
            opacity: 1;
        }

        .tooltip::before {
            content: "";
            position: absolute;
            left: -6px;
            top: 50%;
            transform: translateY(-50%);
            border-width: 6px 6px 6px 0;
            border-style: solid;
            border-color: transparent #1d1d1f transparent transparent;
        }

        @media (max-width: 768px) {
            .tooltip {
                left: 0;
                top: 100%;
                transform: none;
                margin-left: 0;
                margin-top: 8px;
                width: 100%;
            }

            .tooltip::before {
                left: 20px;
                top: -6px;
                transform: none;
                border-width: 0 6px 6px 6px;
                border-color: transparent transparent #1d1d1f transparent;
            }
        }

        /* Style for full-width items in the grid */
        .full-width-card,
        .full-width-section {
            grid-column: 1 / -1; /* Span full width */
        }

        /* Specific styling for full-width sections (non-cards) */
        .full-width-section {
             padding: 2rem 0; /* Adjust padding as needed, remove card styles */
             border: none;
             border-radius: 0;
             background: none;
             box-shadow: none;
        }
         .full-width-section h3 {
             /* Style title similar to card titles but maybe centered or different border */
             color: #1d1d1f;
             margin-bottom: 1.5rem;
             font-size: 1.25rem;
             font-weight: 600;
             letter-spacing: -0.02em;
             text-align: center; /* Center title like Image 1 */
             border-bottom: 1px solid #e5e5e5; /* Add a separator */
             padding-bottom: 1rem;
         }
         .full-width-section h3::before {
             display: none; /* Remove the blue line from card titles */
         }

        /* Results Section Styling */
        #resultsSection {
            margin-top: 4rem;
            padding: 2rem;
            border: 1px solid #e5e5e5;
            border-radius: 16px;
        }

        #resultsSection h2 {
            color: #1d1d1f;
            margin-bottom: 2rem;
            font-size: 1.75rem;
            font-weight: 600;
            letter-spacing: -0.02em;
            text-align: center;
        }

        .results-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }

        .result-card {
            background: #ffffff;
            border: 1px solid #e5e5e5;
            border-radius: 16px;
            padding: 2rem;
            transition: transform 0.2s ease;
        }

        .result-card:hover {
            transform: translateY(-2px);
        }

        .result-card h3 {
            color: #1d1d1f;
            margin-bottom: 1.5rem;
            font-size: 1.25rem;
            font-weight: 600;
            letter-spacing: -0.02em;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .result-card h3::before {
            content: "";
            display: inline-block;
            width: 4px;
            height: 20px;
            background: #007AFF;
            border-radius: 2px;
        }

        .metric-value {
            font-size: 2.5rem;
            font-weight: 700;
            color: #007AFF;
            margin: 1rem 0;
            text-align: center;
        }

        .metric-label {
            color: #666;
            font-size: 0.9rem;
            text-align: center;
            margin-bottom: 1.5rem;
        }

        .score-bar {
            height: 8px;
            background: #f5f5f5;
            border-radius: 4px;
            margin: 1rem 0;
            overflow: hidden;
        }

        .score-fill {
            height: 100%;
            background: #007AFF;
            transition: width 0.5s ease;
        }

        .breakdown-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #f1f1f1;
            font-size: 0.95em;
        }

        .breakdown-item:last-child {
            border-bottom: none;
        }

        .breakdown-label {
            font-weight: 500;
            color: #1d1d1f;
        }

        .breakdown-value {
            color: #007AFF;
            font-weight: 600;
        }

        .age-comparison {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 1.5rem 0;
            padding: 1rem;
            background: #f8f8f8;
            border-radius: 12px;
        }

        .age-value {
            text-align: center;
        }

        .age-value .value {
            font-size: 2rem;
            font-weight: 700;
            color: #1d1d1f;
        }

        .age-value .label {
            font-size: 0.9rem;
            color: #666;
            margin-top: 0.5rem;
        }

        .age-difference {
            font-size: 1.5rem;
            font-weight: 600;
            color: #007AFF;
        }

        @media (max-width: 768px) {
            .results-container {
                grid-template-columns: 1fr;
            }

            .metric-value {
                font-size: 2rem;
            }

            .age-comparison {
                flex-direction: column;
                gap: 1rem;
            }
        }

        /* Detailed Breakdown List */
        .breakdown-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #f1f1f1;
            font-size: 0.95em;
        }

        /* --- Gauge Styling --- */
        .gauge-metric {
            margin-bottom: 30px; /* More space between BMI and WHR gauges */
            position: relative;
        }

        .gauge-label {
            font-size: 1.05em; /* Slightly larger label */
            color: #333;
            margin-bottom: 10px;
            font-weight: 600;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .gauge-container {
            position: relative;
            width: 100%;
            margin-bottom: 8px; /* Less space below gauge */
            padding: 8px 0; /* Add padding above/below for marker visibility */
        }

        .gauge-bar {
            width: 100%;
            height: 12px; /* Thicker bar (increased from 8px) */
            border-radius: 10px;
            /* More refined color gradient with smoother transitions */
            background: linear-gradient(to right,
                #e74c3c 0%, #e74c3c 16.66%, /* Red - High Risk (left) - more vibrant */
                #f39c12 16.66%, #f39c12 33.33%, /* Orange - Moderate Risk - more vibrant */
                #27ae60 33.33%, #27ae60 66.66%, /* Green - Healthy / Low Risk (center) - more vibrant */
                #f39c12 66.66%, #f39c12 83.33%, /* Orange - Moderate Risk - more vibrant */
                #e74c3c 83.33%, #e74c3c 100% /* Red - High Risk (right) - more vibrant */
            );
            box-shadow: 0 1px 2px rgba(0,0,0,0.1);
        }

        .gauge-marker {
            position: absolute;
            top: 8px; /* Position at the bar, not below it */
            left: 50%; /* Default position, JS will override */
            transform: translateX(-50%);
            width: 0;
            height: 0;
            border-left: 4px solid transparent;
            border-right: 4px solid transparent;
            border-top: 6px solid #333; /* Darker triangle for better visibility */
            z-index: 2; /* Ensure marker is above the bar */
        }

        .gauge-interpretation {
            font-size: 0.85em;
            color: #555;
            margin-top: 8px;
            font-weight: 400;
        }

        /* Add scale markers below the gauge for reference */
        .gauge-scale {
            display: flex;
            justify-content: space-between;
            margin-top: 2px;
            padding: 0 2px;
            font-size: 0.7em;
            color: #888;
        }

        /* Improve the Body Composition section container */
        #bodyMeasurementsSection {
            padding: 2rem;
            margin-top: 2rem;
            margin-bottom: 2rem;
            background: #fafafa;
            border-radius: 12px;
        }

        #bodyMeasurementsSection h3 {
            color: #333;
            margin-bottom: 1.5rem;
        }

        #bodyMeasurements {
            max-width: 700px; /* Limit width for better readability */
            margin: 0 auto; /* Center content */
        }

        /* Style the gauge category/risk level information */
        .gauge-metric .gauge-category {
            font-weight: 600;
            color: #333;
        }

        /* Add a subtle outer container for the gauge */
        .gauge-outer {
            background: #ffffff;
            border-radius: 8px;
            padding: 15px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            margin-bottom: 5px;
        }
        /* --- End Gauge Styling --- */

        /* --- Age Impact Factors Section --- */
        .impact-factors-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
            margin-top: 1.5rem;
        }

        .impact-column {
            background: #ffffff;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            border: 1px solid #e5e5e5;
        }
        
        .impact-column:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }

        .impact-column-header {
            display: flex;
            align-items: center;
            margin-bottom: 1.25rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid #f0f0f0;
        }

        .impact-column-header h4 {
            font-size: 1.1rem;
            font-weight: 600;
            margin: 0;
            letter-spacing: -0.01em;
            color: #1d1d1f;
        }

        .impact-column-header .icon {
            width: 36px;
            height: 36px;
            margin-right: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
        }

        .impact-positive .icon {
            background: rgba(39, 174, 96, 0.1);
            color: #27ae60;
        }

        .impact-negative .icon {
            background: rgba(231, 76, 60, 0.1);
            color: #e74c3c;
        }

        .impact-factor {
            display: flex;
            padding: 12px 0;
            border-bottom: 1px solid #f5f5f5;
            min-height: 44px; /* Apple's minimum touch target size */
            align-items: flex-start;
            transition: background-color 0.15s ease;
        }

        .impact-factor:hover {
            background-color: #f9f9f9;
        }

        .impact-factor:last-child {
            border-bottom: none;
        }

        .factor-icon {
            width: 44px;
            height: 44px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 12px;
            color: #555;
        }

        .factor-content {
            flex: 1;
        }

        .factor-name {
            font-weight: 600;
            font-size: 0.95rem;
            color: #1d1d1f;
            margin-bottom: 6px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .factor-impact {
            display: inline-block;
            font-weight: 700;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 0.85rem;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
        }

        .impact-negative .factor-impact {
            background: rgba(231, 76, 60, 0.1);
            color: #e74c3c;
        }

        .impact-positive .factor-impact {
            background: rgba(39, 174, 96, 0.1);
            color: #27ae60;
        }

        .factor-description {
            font-size: 0.85rem;
            color: #666;
            line-height: 1.5;
        }

        #ageImpactSection {
            background: #fafafa;
            border-radius: 16px;
            padding: 2rem;
            margin: 2rem 0;
            box-shadow: 0 1px 3px rgba(0,0,0,0.03);
        }
        
        #ageImpactSection h3 {
            position: relative;
            padding-bottom: 1rem;
            margin-bottom: 1.5rem;
            text-align: center;
            font-weight: 600;
        }
        
        #ageImpactSection h3:after {
            content: "";
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 3px;
            background: #007AFF;
            border-radius: 3px;
        }

        @media (max-width: 768px) {
            .impact-factors-container {
                grid-template-columns: 1fr;
            }
            
            .impact-column:first-child {
                margin-bottom: 1rem;
            }
        }
        /* --- End Age Impact Factors Section --- */
    </style>

    <!-- JavaScript for Calculations and Form Handling -->
    <script>
    (function($) {
        // --- Configuration & Debugging ---

        // Set to true to enable detailed console logging for debugging.
        const DEBUG = true;

        /**
         * Helper function for conditional console logging.
         * Only outputs messages if DEBUG is true.
         * @param {string} message - The message to log.
         * @param {*} [data=null] - Optional data to log alongside the message.
         */
        function debug(message, data = null) {
            if (DEBUG) {
                if (data) {
                    console.log("DEBUG: " + message, data);
                } else {
                    console.log("DEBUG: " + message);
                }
            }
        }

        // --- Calculation Weights ---
        // These weights determine the impact of each metric on the age shift calculation.
        // A higher weight means the metric has a larger influence.
        // The age shift is calculated based on the difference between the user's score and the average score (3).
        // Adjust these values based on research or desired emphasis for each factor.
        const weights = {
            physicalActivity: 0.2,    // Impact of physical activity level
            sleepDuration: 0.15,   // Impact of average sleep hours
            sleepQuality: 0.15,    // Impact of perceived sleep quality
            stressLevels: 0.1,     // Impact of perceived stress level
            socialConnections: 0.05, // Impact of social interaction frequency/quality
            dietQuality: 0.2,      // Impact of overall diet healthiness
            alcoholConsumption: 0.05, // Impact of alcohol intake frequency/amount
            smokingStatus: 0.1,      // Impact of smoking habits
            cognitiveActivity: 0.05, // Impact of mentally stimulating activities
            sunlightExposure: 0.03,  // Impact of daily sunlight exposure
            supplementIntake: 0.02,  // Impact of regular supplement use
            bmiScore: 0.1,         // Impact of Body Mass Index score
            whrScore: 0.1,         // Impact of Waist-to-Hip Ratio score
            sitToStand: 0.05,      // Impact of sit-to-stand test performance
            breathHold: 0.03,      // Impact of breath-holding capacity
            balance: 0.05,         // Impact of balance test performance
            skinElasticity: 0.02,  // Impact of skin elasticity test
            overallHealthScore: 0.1  // Impact of a general health score (currently defaulted to 3)
        };

        // --- Core Calculation Functions ---

        /**
         * Calculates Body Mass Index (BMI).
         * Formula: weight (kg) / (height (m))^2
         * @param {number} heightCm - Height in centimeters.
         * @param {number} weightKg - Weight in kilograms.
         * @returns {number|NaN} - Calculated BMI or NaN if inputs are invalid.
         */
        function calculateBMI(heightCm, weightKg) {
            // Basic validation for non-zero positive inputs
            if (!heightCm || heightCm <= 0 || !weightKg || weightKg <= 0) return NaN;
            // Convert height to meters for the calculation
            return weightKg / ((heightCm / 100) ** 2);
        }

        /**
         * Calculates Waist-to-Hip Ratio (WHR).
         * Formula: waist circumference / hip circumference
         * @param {number} waistCm - Waist circumference in centimeters.
         * @param {number} hipCm - Hip circumference in centimeters.
         * @returns {number|NaN} - Calculated WHR or NaN if inputs are invalid.
         */
        function calculateWHR(waistCm, hipCm) {
            // Basic validation for non-zero positive inputs
            if (!waistCm || waistCm <= 0 || !hipCm || hipCm <= 0) return NaN; // Prevent division by zero/invalid input
            return waistCm / hipCm;
        }

        /**
         * Determines a score (0-5) based on the calculated BMI.
         * Higher scores generally indicate healthier BMI ranges.
         * Ranges:
         * < 18.5: 1 (Underweight)
         * 18.5 - 19.9: 3
         * 20.0 - 22.0: 5 (Optimal)
         * 22.1 - 25.0: 4
         * 25.1 - 27.5: 3
         * 27.6 - 30.0: 2
         * > 30.0: 1 (Obese)
         * @param {number} bmi - Calculated BMI value.
         * @returns {number} - Score from 0 (invalid) to 5.
         */
        function getBMIScore(bmi) {
            if (isNaN(bmi)) return 0; // Handle invalid BMI input
            if (bmi < 18.5) return 1;
            if (bmi < 20) return 3;
            if (bmi <= 22) return 5;
            if (bmi <= 25) return 4;
            if (bmi <= 27.5) return 3;
            if (bmi <= 30) return 2;
            return 1;
        }

        /**
         * Determines a score (0-5) based on the calculated WHR and gender.
         * Lower WHR values generally indicate lower health risks.
         * Different thresholds are used for males and females.
         * @param {number} whr - Calculated WHR value.
         * @param {string} gender - User's selected gender ("male", "female", "other").
         * @returns {number} - Score from 0 (invalid) to 5.
         */
        function getWHRScore(whr, gender) {
            if (isNaN(whr) || !gender) return 0; // Handle invalid WHR or missing gender
            const lowerCaseGender = gender.toLowerCase(); // Ensure case-insensitivity

            // Female WHR Score Ranges:
            if (lowerCaseGender === "female") {
                if (whr <= 0.75) return 5; // Optimal
                if (whr <= 0.80) return 4;
                if (whr <= 0.85) return 3;
                if (whr <= 0.90) return 2;
                return 1; // High risk
            } else { // Male (and Other) WHR Score Ranges:
                // Using male standards as default for "other" or non-female inputs
                if (whr <= 0.85) return 5; // Optimal
                if (whr <= 0.90) return 4;
                if (whr <= 0.95) return 3;
                if (whr <= 1.00) return 2;
                return 1; // High risk
            }
        }

        /**
         * Calculates the estimated shift in biological age relative to chronological age.
         * A positive shift suggests accelerated aging, a negative shift suggests slower aging.
         * Calculated by summing the weighted differences between each score and the average (3).
         * @param {object} scores - Object containing all user scores (lifestyle, BMI, WHR, etc.).
         * @param {number} age - User's chronological age.
         * @returns {number} - The calculated age shift in years.
         */
        function calculateAgeShift(scores, age) {
            let totalShift = 0;
            debug("Calculating age shift. Initial age:", age);
            debug("Scores being used for shift:", scores);
            debug("Weights used:", weights);

            // Iterate through each metric defined in the weights object
            for (let metric in weights) {
                const score = scores[metric]; // Get the user's score for this metric
                // Check if the score is a valid number
                if (typeof score === 'number' && !isNaN(score)) {
                    // Calculate the shift contribution for this metric
                    // Formula: weight * (average_score - user_score)
                    // A score above 3 results in a negative shift (good), below 3 is positive (bad)
                    const shiftContribution = weights[metric] * (3 - score);
                    totalShift += shiftContribution;
                    debug(`Metric: ${metric}, Score: ${score}, Weight: ${weights[metric]}, Shift Contribution: ${shiftContribution.toFixed(2)}`);
                } else {
                    // Log if a score is missing or invalid for a weighted metric
                    debug(`Invalid or missing score for metric: ${metric}. Skipping.`);
                }
            }
             debug("Total shift before age adjustment:", totalShift.toFixed(2));

            // Apply age-based adjustments to make the shift more realistic
            // Younger individuals (<25) with good scores (negative shift) have the effect reduced.
            if (age < 25 && totalShift < 0) {
                 // Reduce the negative shift, capped by a percentage of their age.
                 const adjustment = Math.max(totalShift * 0.3, -(age * 0.2));
                 debug(`Age < 25 adjustment: ${adjustment.toFixed(2)} (from ${totalShift.toFixed(2)})`);
                 totalShift = adjustment;
            } else if (age < 35) {
                 // Reduce the overall shift (positive or negative) for individuals under 35.
                 const adjustment = totalShift * 0.5;
                 debug(`Age < 35 adjustment: ${adjustment.toFixed(2)} (from ${totalShift.toFixed(2)})`);
                 totalShift = adjustment;
            } else if (age > 65) {
                 // Reduce the overall shift for individuals over 65.
                 const adjustment = totalShift * 0.7;
                 debug(`Age > 65 adjustment: ${adjustment.toFixed(2)} (from ${totalShift.toFixed(2)})`);
                 totalShift = adjustment;
            }
             debug("Final age shift:", totalShift.toFixed(2));
            return totalShift;
        }

        /**
         * Calculates the estimated biological age.
         * Formula: chronologicalAge + (ageShift * scalingFactor)
         * @param {number} chronologicalAge - User's actual age.
         * @param {number} ageShift - Calculated age shift from calculateAgeShift().
         * @returns {number|NaN} - Estimated biological age or NaN if inputs invalid.
         */
        function calculateBiologicalAge(chronologicalAge, ageShift) {
            // Scaling factor to moderate the impact of the age shift
            const scalingFactor = 0.8;
            if (isNaN(chronologicalAge) || isNaN(ageShift)) return NaN; // Check inputs
            const bioAge = chronologicalAge + (ageShift * scalingFactor);
            debug(`Calculated Biological Age: ${bioAge.toFixed(1)} (Chrono: ${chronologicalAge}, Shift: ${ageShift.toFixed(2)}, Scale: ${scalingFactor})`);
            return bioAge;
        }

        /**
         * Calculates the aging rate.
         * Formula: biologicalAge / chronologicalAge
         * A rate > 1 suggests faster aging, < 1 suggests slower aging.
         * @param {number} biologicalAge - Calculated biological age.
         * @param {number} chronologicalAge - User's actual age.
         * @returns {number|NaN} - Aging rate or NaN if inputs invalid.
         */
        function calculateAgingRate(biologicalAge, chronologicalAge) {
            // Ensure chronological age is valid and positive for division
            if (isNaN(biologicalAge) || !chronologicalAge || chronologicalAge <= 0) return NaN; // Check inputs
            const rate = biologicalAge / chronologicalAge;
            debug(`Calculated Aging Rate: ${rate.toFixed(2)} (BioAge: ${biologicalAge.toFixed(1)}, ChronoAge: ${chronologicalAge})`);
            return rate;
        }

        // --- Display & Form Handling ---

        /**
         * Displays the calculated results in the HTML.
         * Finds the results section and populates the relevant divs.
         * @param {object} scores - Object containing all user scores.
         * @param {object} measurements - Object containing height, weight, waist, hip, gender.
         * @param {number} age - User's chronological age.
         */
        function displayResults(scores, measurements, age) {
            debug("Displaying results...");
            // Get the main results container element
            const resultsSection = document.getElementById('resultsSection');

            // Ensure the results section exists in the DOM
            if (!resultsSection) {
                console.error("Results section element with ID 'resultsSection' not found!");
                return; // Stop if the container isn't found
            }

            // Make the results section visible
            resultsSection.style.display = 'block';

            // --- Calculate Core Metrics ---
            const bmi = calculateBMI(measurements.height, measurements.weight);
            const whr = calculateWHR(measurements.waist, measurements.hip);
            const bmiScore = getBMIScore(bmi);
            const whrScore = getWHRScore(whr, measurements.gender);

            debug("BMI calculated:", { value: bmi, score: bmiScore });
            debug("WHR calculated:", { value: whr, score: whrScore });

            // Add calculated BMI/WHR scores to the main scores object
            scores.bmiScore = bmiScore;
            scores.whrScore = whrScore;
            // Assign a default overallHealthScore (since it's not collected from the form)
            scores.overallHealthScore = 3;

            // Calculate Age-related metrics
            const ageShift = calculateAgeShift(scores, age);
            const biologicalAge = calculateBiologicalAge(age, ageShift);
            const agingRate = calculateAgingRate(biologicalAge, age);

            // --- Populate Biological Age Card ---
            const biologicalAgeDiv = document.getElementById('biologicalAgeDisplay');
            if (biologicalAgeDiv) {
                // Display Biological Age and the Age Shift in parentheses
                const ageShiftText = !isNaN(ageShift) ? ` (${ageShift > 0 ? '+' : ''}${ageShift.toFixed(1)} years)` : '';
                biologicalAgeDiv.innerHTML = `
                    <div class="metric-value">${!isNaN(biologicalAge) ? biologicalAge.toFixed(1) : 'N/A'} years</div>
                    <div class="metric-label">vs Chronological Age: ${age} ${ageShiftText}</div>
                `;
                debug("Biological Age HTML updated.");
            } else {
                console.error("Element with ID 'biologicalAgeDisplay' not found!");
            }

            // --- Populate Lifestyle Score Card ---
            const allScores = Object.values(scores);
            const validScores = allScores.filter(score => typeof score === 'number' && !isNaN(score));
            const lifestyleScore = validScores.length > 0 ? validScores.reduce((a, b) => a + b, 0) / validScores.length : NaN;
            debug("Lifestyle Score calculated:", { value: lifestyleScore, count: validScores.length });

            const lifestyleScoreDiv = document.getElementById('lifestyleScore');
             if (lifestyleScoreDiv) {
                lifestyleScoreDiv.innerHTML = `
                    <div class="metric-value">${!isNaN(lifestyleScore) ? lifestyleScore.toFixed(1) : 'N/A'}/5</div>
                    <div class="metric-label">Overall Lifestyle Score</div>
                    <div class="score-bar">
                        <div class="score-fill" style="width: ${!isNaN(lifestyleScore) ? (lifestyleScore/5)*100 : 0}%" ></div>
                    </div>
                `;
                 debug("Lifestyle Score HTML updated.");
            } else { console.error("Element with ID 'lifestyleScore' not found!"); }

             // --- Populate Aging Rate Card ---
             const agingRateDiv = document.getElementById('agingRateDisplay');
             if(agingRateDiv) {
                let rateText = 'N/A';
                let interpretation = '';
                if (!isNaN(agingRate)) {
                    rateText = agingRate.toFixed(2);
                    if (agingRate > 1.05) interpretation = '(Faster than average)';
                    else if (agingRate < 0.95) interpretation = '(Slower than average)';
                    else interpretation = '(Average rate)';
                }
                 agingRateDiv.innerHTML = `
                    <div class="metric-value">${rateText}</div>
                    <div class="metric-label">Aging Rate ${interpretation}</div>
                `;
                 debug("Aging Rate HTML updated.");
             } else {
                 console.error("Element with ID 'agingRateDisplay' not found!");
             }

            // --- Populate Body Measurements Card (with Gauges) ---
            const bodyMeasurementsDiv = document.getElementById('bodyMeasurements');
            if (bodyMeasurementsDiv) {
                let bmiHtml = '';
                let whrHtml = '';

                if (!isNaN(bmi)) {
                    // Calculate BMI marker position (Example range 15-40 -> 0-100%)
                    const bmiMin = 15;
                    const bmiMax = 40;
                    let bmiPercent = ((bmi - bmiMin) / (bmiMax - bmiMin)) * 100;
                    bmiPercent = Math.max(0, Math.min(100, bmiPercent)); // Clamp between 0 and 100
                    let bmiInterpretation = getBMICategory(bmi);

                    bmiHtml = `
                        <div class="gauge-outer">
                            <div class="gauge-metric">
                                <div class="gauge-label">
                                    <span>BMI: ${bmi.toFixed(1)}</span>
                                    <span class="gauge-category">Category: ${bmiInterpretation}</span>
                                </div>
                                <div class="gauge-container">
                                    <div class="gauge-bar"></div>
                                    <div class="gauge-marker" style="left: ${bmiPercent}%;"></div>
                                </div>
                                <div class="gauge-scale">
                                    <span>15</span>
                                    <span>20</span>
                                    <span>25</span>
                                    <span>30</span>
                                    <span>35</span>
                                    <span>40</span>
                                </div>
                                <div class="gauge-interpretation">
                                    BMI (Body Mass Index) measures weight relative to height. A BMI between 18.5-24.9 is considered healthy.
                                </div>
                            </div>
                        </div>
                    `;
                } else {
                    bmiHtml = `<div class="gauge-metric"><div class="gauge-label">BMI: N/A</div></div>`;
                }

                if (!isNaN(whr)) {
                    // Calculate WHR marker position (Gender specific ranges, example)
                    const isFemale = measurements.gender.toLowerCase() === 'female';
                    const whrMin = isFemale ? 0.65 : 0.80;
                    const whrMax = isFemale ? 1.0 : 1.15;
                    let whrPercent = ((whr - whrMin) / (whrMax - whrMin)) * 100;
                    whrPercent = Math.max(0, Math.min(100, whrPercent)); // Clamp
                    let whrInterpretation = getWHRCategory(whr, measurements.gender);

                    whrHtml = `
                        <div class="gauge-outer">
                            <div class="gauge-metric">
                                <div class="gauge-label">
                                    <span>WHR: ${whr.toFixed(2)}</span>
                                    <span class="gauge-category">Risk Level: ${whrInterpretation}</span>
                                </div>
                                <div class="gauge-container">
                                    <div class="gauge-bar"></div>
                                    <div class="gauge-marker" style="left: ${whrPercent}%;"></div>
                                </div>
                                <div class="gauge-scale">
                                    <span>${isFemale ? '0.65' : '0.80'}</span>
                                    <span>${isFemale ? '0.75' : '0.90'}</span>
                                    <span>${isFemale ? '0.85' : '0.95'}</span>
                                    <span>${isFemale ? '0.95' : '1.05'}</span>
                                    <span>${isFemale ? '1.0' : '1.15'}</span>
                                </div>
                                <div class="gauge-interpretation">
                                    WHR (Waist-to-Hip Ratio) measures body fat distribution. ${isFemale ? 'For women, a WHR of 0.8 or less indicates low health risk.' : 'For men, a WHR of 0.95 or less indicates low health risk.'}
                                </div>
                            </div>
                        </div>
                    `;
                } else {
                    whrHtml = `<div class="gauge-metric"><div class="gauge-label">WHR: N/A</div></div>`;
                }

                bodyMeasurementsDiv.innerHTML = bmiHtml + whrHtml;
                debug("Body Measurements HTML updated with gauges.");

            } else {
                console.error("Element with ID 'bodyMeasurements' not found!");
            }

            // --- Populate Age Impact Factors Section ---
            // This will identify the top factors impacting the biological age (both positive and negative)
            function generateAgeImpactFactors(scores) {
                // Define factor descriptions and icons
                const factorDetails = {
                    physicalActivity: {
                        name: "Physical Activity",
                        icon: "directions_run",
                        descriptions: {
                            positive: "Regular physical activity helps maintain muscle mass, cardiovascular health, and metabolic function.",
                            negative: "Low levels of physical activity can lead to decreased muscle mass, cardiovascular health issues, and metabolic dysfunction."
                        }
                    },
                    sleepDuration: {
                        name: "Sleep Duration",
                        icon: "bedtime",
                        descriptions: {
                            positive: "Optimal sleep duration supports cellular repair, hormone regulation, and cognitive function.",
                            negative: "Insufficient sleep impairs cellular repair, disrupts hormone balance, and accelerates cognitive aging."
                        }
                    },
                    sleepQuality: {
                        name: "Sleep Quality",
                        icon: "nightlight",
                        descriptions: {
                            positive: "High quality sleep enables deeper restorative processes and better hormonal regulation.",
                            negative: "Poor sleep quality prevents deep restorative processes and disrupts hormonal balance."
                        }
                    },
                    stressLevels: {
                        name: "Stress Management",
                        icon: "spa",
                        descriptions: {
                            positive: "Effective stress management protects telomeres and reduces inflammation.",
                            negative: "Chronic stress shortens telomeres and increases systemic inflammation."
                        }
                    },
                    socialConnections: {
                        name: "Social Connections",
                        icon: "people",
                        descriptions: {
                            positive: "Strong social connections support immune function and reduce chronic stress.",
                            negative: "Social isolation is linked to increased inflammation and stress hormones."
                        }
                    },
                    dietQuality: {
                        name: "Diet Quality",
                        icon: "restaurant",
                        descriptions: {
                            positive: "A nutrient-rich diet provides antioxidants and anti-inflammatory compounds.",
                            negative: "Poor diet quality increases oxidative stress and inflammation."
                        }
                    },
                    alcoholConsumption: {
                        name: "Alcohol Consumption",
                        icon: "liquor",
                        descriptions: {
                            positive: "Minimal alcohol intake reduces liver stress and cellular damage.",
                            negative: "Excessive alcohol consumption damages cells and accelerates aging processes."
                        }
                    },
                    smokingStatus: {
                        name: "Smoking Status",
                        icon: "smoke_free",
                        descriptions: {
                            positive: "Being smoke-free preserves lung function and reduces oxidative damage.",
                            negative: "Smoking causes extensive oxidative damage and accelerates cellular aging."
                        }
                    },
                    cognitiveActivity: {
                        name: "Cognitive Activity",
                        icon: "psychology",
                        descriptions: {
                            positive: "Regular mental stimulation builds cognitive reserve and neural connections.",
                            negative: "Limited cognitive engagement leads to faster cognitive decline."
                        }
                    },
                    sunlightExposure: {
                        name: "Sunlight Exposure",
                        icon: "wb_sunny",
                        descriptions: {
                            positive: "Balanced sunlight exposure supports vitamin D production and circadian rhythm.",
                            negative: "Inadequate sunlight affects vitamin D levels and disrupts sleep patterns."
                        }
                    },
                    supplementIntake: {
                        name: "Supplement Use",
                        icon: "medication",
                        descriptions: {
                            positive: "Strategic supplementation addresses nutritional gaps and supports cellular health.",
                            negative: "Lack of key nutrients can impair cellular function and repair mechanisms."
                        }
                    },
                    bmiScore: {
                        name: "Body Mass Index",
                        icon: "monitor_weight",
                        descriptions: {
                            positive: "Healthy BMI supports metabolic health and reduces inflammation.",
                            negative: "Suboptimal BMI increases inflammation and metabolic burden."
                        }
                    },
                    whrScore: {
                        name: "Waist-to-Hip Ratio",
                        icon: "straighten",
                        descriptions: {
                            positive: "Balanced fat distribution indicates lower visceral fat and inflammation.",
                            negative: "Higher WHR suggests increased visceral fat, which produces inflammatory compounds."
                        }
                    },
                    sitToStand: {
                        name: "Functional Strength",
                        icon: "accessibility_new",
                        descriptions: {
                            positive: "Good functional strength supports independence and reduces fall risk.",
                            negative: "Poor functional strength increases dependence and risk of injuries."
                        }
                    },
                    breathHold: {
                        name: "Respiratory Function",
                        icon: "air",
                        descriptions: {
                            positive: "Strong respiratory function indicates good lung capacity and oxygen exchange.",
                            negative: "Limited breath hold capacity may indicate reduced lung function."
                        }
                    },
                    balance: {
                        name: "Balance Ability",
                        icon: "airline_seat_recline_normal",
                        descriptions: {
                            positive: "Good balance indicates strong neuromuscular coordination and reduces fall risk.",
                            negative: "Poor balance suggests neuromuscular decline and increased injury risk."
                        }
                    },
                    skinElasticity: {
                        name: "Skin Health",
                        icon: "face",
                        descriptions: {
                            positive: "Good skin elasticity reflects collagen maintenance and hydration.",
                            negative: "Reduced skin elasticity indicates collagen breakdown and dehydration."
                        }
                    }
                };

                // Calculate the impact values (difference from average)
                const impactValues = {};
                
                // For each factor, calculate the difference from the average score (3)
                // Multiply by the weight to get the actual impact
                for (let factor in scores) {
                    if (factor in weights && factor in factorDetails) {
                        const score = scores[factor];
                        // Calculate impact (negative values mean they're adding age, positive values mean reducing age)
                        // This is because higher scores are better, and we want to show how much they add/subtract from age
                        impactValues[factor] = weights[factor] * (score - 3);
                    }
                }
                
                // Sort factors by impact (absolute value)
                const sortedFactors = Object.keys(impactValues)
                    .filter(factor => factorDetails[factor]) // Ensure we have details for this factor
                    .sort((a, b) => Math.abs(impactValues[b]) - Math.abs(impactValues[a]));
                
                // Separate positive and negative factors
                const positiveFactors = sortedFactors.filter(factor => impactValues[factor] > 0);
                const negativeFactors = sortedFactors.filter(factor => impactValues[factor] < 0);
                
                // Take top 3 of each (or fewer if there aren't 3)
                const topPositive = positiveFactors.slice(0, 3);
                const topNegative = negativeFactors.slice(0, 3);
                
                // Generate HTML for positive factors
                let positiveHtml = '';
                topPositive.forEach(factor => {
                    const details = factorDetails[factor];
                    const impact = impactValues[factor];
                    positiveHtml += `
                        <div class="impact-factor">
                            <div class="factor-icon">
                                <span class="material-icons">
                                    ${details.icon}
                                </span>
                            </div>
                            <div class="factor-content">
                                <div class="factor-name">
                                    <span>${details.name}</span>
                                    <span class="factor-impact impact-positive">-${Math.abs(impact).toFixed(1)} yrs</span>
                                </div>
                                <div class="factor-description">
                                    ${details.descriptions.positive}
                                </div>
                            </div>
                        </div>
                    `;
                });
                
                // If no positive factors found, add a placeholder
                if (topPositive.length === 0) {
                    positiveHtml = `
                        <div class="impact-factor">
                            <div class="factor-content">
                                <div class="factor-description" style="text-align: center; padding: 20px 0;">
                                    No significant positive factors identified. Consider improving your lifestyle scores.
                                </div>
                            </div>
                        </div>
                    `;
                }
                
                // Generate HTML for negative factors
                let negativeHtml = '';
                topNegative.forEach(factor => {
                    const details = factorDetails[factor];
                    const impact = impactValues[factor];
                    negativeHtml += `
                        <div class="impact-factor">
                            <div class="factor-icon">
                                <span class="material-icons">
                                    ${details.icon}
                                </span>
                            </div>
                            <div class="factor-content">
                                <div class="factor-name">
                                    <span>${details.name}</span>
                                    <span class="factor-impact impact-negative">+${Math.abs(impact).toFixed(1)} yrs</span>
                                </div>
                                <div class="factor-description">
                                    ${details.descriptions.negative}
                                </div>
                            </div>
                        </div>
                    `;
                });
                
                // If no negative factors found, add a placeholder
                if (topNegative.length === 0) {
                    negativeHtml = `
                        <div class="impact-factor">
                            <div class="factor-content">
                                <div class="factor-description" style="text-align: center; padding: 20px 0;">
                                    Great job! No significant negative factors identified.
                                </div>
                            </div>
                        </div>
                    `;
                }
                
                return {
                    positive: positiveHtml,
                    negative: negativeHtml
                };
            }

            const impactFactorsHtml = generateAgeImpactFactors(scores);
            const positiveFactorsDiv = document.querySelector('#ageImpactSection .impact-column:first-child');
            const negativeFactorsDiv = document.querySelector('#ageImpactSection .impact-column:last-child');
            
            if (positiveFactorsDiv && negativeFactorsDiv) {
                // Update the content of the columns, preserving the headers
                const positiveHeader = positiveFactorsDiv.querySelector('.impact-column-header');
                const negativeHeader = negativeFactorsDiv.querySelector('.impact-column-header');
                
                positiveFactorsDiv.innerHTML = '';
                negativeFactorsDiv.innerHTML = '';
                
                // Add headers back
                positiveFactorsDiv.appendChild(positiveHeader);
                negativeFactorsDiv.appendChild(negativeHeader);
                
                // Add the new factor content
                positiveFactorsDiv.innerHTML += impactFactorsHtml.positive;
                negativeFactorsDiv.innerHTML += impactFactorsHtml.negative;
                
                debug("Age Impact Factors HTML updated.");
            } else {
                console.error("Age Impact Factors columns not found!");
            }

            // --- Populate Detailed Breakdown Card ---
            let breakdownHTML = '';
            const breakdownKeys = Object.keys(weights);

            for (let metric of breakdownKeys) {
                const score = scores[metric];
                const label = metric.replace(/([A-Z])/g, ' $1').replace(/^./, str => str.toUpperCase());
                breakdownHTML += `
                    <div class="breakdown-item">
                        <span class="breakdown-label">${label}</span>
                        <span class="breakdown-value">${typeof score === 'number' && !isNaN(score) ? score : 'N/A'}/5</span>
                    </div>
                `;
            }
            const detailedBreakdownDiv = document.getElementById('detailedBreakdown');
             if (detailedBreakdownDiv) {
                detailedBreakdownDiv.innerHTML = breakdownHTML;
                 debug("Detailed Breakdown HTML updated.");
            } else { console.error("Element with ID 'detailedBreakdown' not found!"); }

            // --- Scroll to Results ---
            resultsSection.scrollIntoView({ behavior: 'smooth' });
             debug("Scrolled to results section.");
        }

        /**
         * NEW Helper: Determines BMI category text.
         * @param {number} bmi - Calculated BMI.
         * @returns {string} - Category description.
         */
        function getBMICategory(bmi) {
            if (isNaN(bmi)) return 'N/A';
            if (bmi < 18.5) return 'Underweight';
            if (bmi < 25) return 'Healthy Weight';
            if (bmi < 30) return 'Overweight';
            return 'Obese';
        }

        /**
         * NEW Helper: Determines WHR risk category text.
         * @param {number} whr - Calculated WHR.
         * @param {string} gender - User gender.
         * @returns {string} - Risk category description.
         */
        function getWHRCategory(whr, gender) {
            if (isNaN(whr) || !gender) return 'N/A';
            const lowerCaseGender = gender.toLowerCase();

            if (lowerCaseGender === "female") {
                if (whr <= 0.80) return 'Low Risk';
                if (whr <= 0.85) return 'Moderate Risk';
                return 'High Risk';
            } else { // Male or Other
                if (whr <= 0.95) return 'Low Risk';
                if (whr <= 1.00) return 'Moderate Risk';
                return 'High Risk';
            }
        }

        /**
         * Sets up the event listener for the form submission.
         * Uses jQuery for potentially better compatibility within WordPress.
         */
        function setupFormListener() {
            // Select the form using its ID with jQuery
            const form = $('#longevityForm');

            // Check if the form was actually found
            if (form.length === 0) {
                console.error("Longevity form not found using jQuery selector '#longevityForm'!");
                return; // Stop if form doesn't exist
            }
            debug("Form found using jQuery, attaching submit listener...");

            // Track if the initial calculation has been done
            let initialCalculationDone = false;

            // Attach the submit event handler
            form.on('submit', function(e) {
                e.preventDefault(); // IMPORTANT: Prevent the default browser form submission
                debug("Form submitted, processing data...");

                // Process form data and display results
                processFormData();
                
                // After the first calculation, set up live updates
                if (!initialCalculationDone) {
                    setupLiveUpdates();
                    initialCalculationDone = true;
                }
            });
            
            /**
             * Process the form data and display results
             */
            function processFormData() {
                // Use the FormData API to easily get all values from the form fields
                const formData = new FormData(form[0]); // form[0] refers to the DOM element

                // Extract measurement values, converting to numbers and providing defaults
                const measurements = {
                    height: parseFloat(formData.get('height')) || 0, // Get 'height' field, convert to float, default to 0
                    weight: parseFloat(formData.get('weight')) || 0, // Get 'weight' field
                    waist: parseFloat(formData.get('waist')) || 0,   // Get 'waist' field
                    hip: parseFloat(formData.get('hip')) || 0,     // Get 'hip' field
                    gender: formData.get('gender'),                 // Get 'gender' field (string)
                    age: parseInt(formData.get('age'), 10) || 0      // Get 'age' field, convert to integer (base 10), default 0
                };

                // Extract score values from select dropdowns, converting to numbers
                // Provides a default value of 3 (average) if a field is missing or not a number.
                const scoreKeys = [
                    'activity', 'sleepDuration', 'sleepQuality', 'stressLevels',
                    'socialConnections', 'dietQuality', 'alcoholConsumption', 'smokingStatus',
                    'cognitiveActivity', 'sunlightExposure', 'supplementIntake', 'sitStand',
                    'breathHold', 'balance', 'skinElasticity'
                ];
                const scores = {};
                scoreKeys.forEach(key => {
                    // Assumes the input field's `name` attribute matches the key.
                    // If names differ (like 'sitStand' vs 'sitStandInput'), adjust here.
                    const inputName = key; // Example: const inputName = (key === 'sitStand') ? 'sitStandCapability' : key;
                    const value = parseInt(formData.get(inputName), 10); // Get value, convert to integer
                    scores[key] = !isNaN(value) ? value : 3; // Use parsed value or default to 3
                });

                debug("Collected Measurements:", measurements);
                debug("Collected Scores:", scores);

                // --- Basic Input Validation ---
                // Add checks here for essential fields before proceeding with calculations.
                if (!measurements.age || measurements.age < 18) {
                    alert("Please enter a valid age (18+).");
                    console.error("Invalid age entered:", measurements.age);
                    return false; // Stop processing and return false to indicate validation failed
                }
                if (!measurements.gender) {
                    alert("Please select your gender.");
                    console.error("Gender not selected");
                    return false; // Stop processing
                }
                // Check for valid positive numbers for measurements involved in division
                if (!measurements.height || measurements.height <= 0 || !measurements.weight || measurements.weight <= 0) {
                    alert("Please enter valid positive numbers for height and weight.");
                    console.error("Invalid height/weight:", measurements);
                    return false;
                }
                if (!measurements.waist || measurements.waist <= 0 || !measurements.hip || measurements.hip <= 0) {
                    alert("Please enter valid positive numbers for waist and hip measurements.");
                    console.error("Invalid waist/hip:", measurements);
                    return false;
                }

                // --- Trigger Calculations & Display ---
                // Pass the collected data to the displayResults function, which handles calculations.
                displayResults(scores, measurements, measurements.age);
                return true; // Return true to indicate success
            }
            
            /**
             * Set up live updates for all form inputs after the initial calculation
             */
            function setupLiveUpdates() {
                debug("Setting up live updates for form inputs...");
                
                // Add change event listeners to all form inputs
                form.find('input, select').on('change', function() {
                    debug("Form input changed, updating results...");
                    
                    // Only update if the results section is already visible
                    if ($('#resultsSection').is(':visible')) {
                        processFormData();
                    }
                });
                
                debug("Live updates configured successfully.");
            }
        }

        // --- Initialization ---
        // Use jQuery's document ready function to ensure the DOM is fully loaded
        // before trying to attach the form listener.
        $(document).ready(function() {
             debug("Document ready, setting up form listener...");
            setupFormListener(); // Call the function to attach the listener to the form
        });

    })(jQuery); // Pass jQuery to the closure to use the `$` alias safely
    </script>
    <?php
    return ob_get_clean();
}
add_shortcode('longevity_form', 'longevity_assessment_form');
?> 
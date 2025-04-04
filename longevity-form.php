// OpenAI API Integration for Longevity Analysis
add_action('wp_ajax_longevity_ai_analysis', 'longevity_ai_analysis_callback');
add_action('wp_ajax_nopriv_longevity_ai_analysis', 'longevity_ai_analysis_callback');

// Initialize OpenAI API Key - Improved security
function longevity_store_openai_api_key() {
    // Only create the option if it doesn't exist
    if (!get_option('longevity_openai_api_key')) {
        update_option('longevity_openai_api_key', '');
    }
}
add_action('init', 'longevity_store_openai_api_key');

// Add admin menu for API key management
function longevity_admin_menu() {
    add_options_page(
        'Longevity Assessment Settings',
        'Longevity Settings',
        'manage_options',
        'longevity-settings',
        'longevity_settings_page'
    );
}
add_action('admin_menu', 'longevity_admin_menu');

// Settings page content
function longevity_settings_page() {
    // Save settings if form is submitted
    if (isset($_POST['longevity_settings_nonce']) && wp_verify_nonce($_POST['longevity_settings_nonce'], 'longevity_save_settings')) {
        if (isset($_POST['openai_api_key'])) {
            update_option('longevity_openai_api_key', sanitize_text_field($_POST['openai_api_key']));
            echo '<div class="notice notice-success"><p>Settings saved successfully!</p></div>';
        }
    }
    
    // Get current API key (show only first/last 4 chars if exists)
    $api_key = get_option('longevity_openai_api_key', '');
    $masked_key = '';
    if (!empty($api_key)) {
        $length = strlen($api_key);
        if ($length > 8) {
            $masked_key = substr($api_key, 0, 4) . str_repeat('•', $length - 8) . substr($api_key, -4);
        } else {
            $masked_key = $api_key; // Key is too short to mask effectively
        }
    }
    
    // Display settings form
    ?>
    <div class="wrap">
        <h1>Longevity Assessment Settings</h1>
        <form method="post" action="">
            <?php wp_nonce_field('longevity_save_settings', 'longevity_settings_nonce'); ?>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="openai_api_key">OpenAI API Key</label></th>
                    <td>
                        <input type="password" id="openai_api_key" name="openai_api_key" 
                               value="<?php echo esc_attr($api_key); ?>" class="regular-text" autocomplete="off">
                        <?php if (!empty($masked_key)): ?>
                            <p class="description">Current key: <?php echo esc_html($masked_key); ?></p>
                        <?php endif; ?>
                        <p class="description">Enter your OpenAI API key for AI analysis functionality.</p>
                    </td>
                </tr>
            </table>
            <p class="submit">
                <input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes">
            </p>
        </form>
    </div>
    <?php
}

function longevity_ai_analysis_callback() {
    // Basic security check
    check_ajax_referer('longevity_form_nonce', 'security');
    
    // Get the OpenAI API key from WordPress options
    $api_key = get_option('longevity_openai_api_key');
    
    // Check if API key exists
    if (!$api_key || $api_key === 'sk-REPLACE_WITH_YOUR_API_KEY') {
        wp_send_json_error(array('message' => 'API key not configured. Please contact the site administrator.'));
        return;
    }
    
    // Get data from the AJAX request
    $analysis_data = isset($_POST['analysis_data']) ? $_POST['analysis_data'] : null;
    
    if (!$analysis_data) {
        wp_send_json_error(array('message' => 'No data provided for analysis.'));
        return;
    }
    
    // Sanitize and prepare data for OpenAI
    $decoded_data = json_decode(stripslashes($analysis_data), true);
    
    // Construct the prompt for OpenAI
    $prompt = "Please analyze this longevity assessment data and provide personalized health insights.\n\n";
    $prompt .= "USER DATA:\n";
    $prompt .= "Age: " . $decoded_data['age'] . "\n";
    $prompt .= "Gender: " . $decoded_data['gender'] . "\n";
    $prompt .= "Biological Age: " . $decoded_data['biologicalAge'] . " years (";
    $prompt .= $decoded_data['ageShift'] > 0 ? "+" : "";
    $prompt .= $decoded_data['ageShift'] . " years from chronological age)\n";
    $prompt .= "Aging Rate: " . $decoded_data['agingRate'] . "\n";
    $prompt .= "BMI: " . $decoded_data['bmi'] . " (" . $decoded_data['bmiCategory'] . ")\n";
    $prompt .= "WHR: " . $decoded_data['whr'] . " (" . $decoded_data['whrCategory'] . ")\n\n";
    
    $prompt .= "SCORES (on scale of 0-5, where 5 is optimal):\n";
    foreach ($decoded_data['scores'] as $metric => $score) {
        // Format the metric name to be more readable
        $formatted_metric = ucwords(preg_replace('/(?<!^)[A-Z]/', ' $0', $metric));
        $prompt .= "$formatted_metric: $score\n";
    }
    
    $prompt .= "\nPOSITIVE FACTORS:\n";
    foreach ($decoded_data['positiveFactors'] as $factor) {
        $prompt .= "- " . $factor['name'] . " (impact: -" . abs($factor['impact']) . " years)\n";
    }
    
    $prompt .= "\nNEGATIVE FACTORS:\n";
    foreach ($decoded_data['negativeFactors'] as $factor) {
        $prompt .= "- " . $factor['name'] . " (impact: +" . abs($factor['impact']) . " years)\n";
    }
    
    $prompt .= "\nBased on this comprehensive health data, please provide:\n";
    $prompt .= "1. A personalized summary of their overall longevity profile, highlighting the connection between their lifestyle choices and biological aging (2-3 sentences)\n";
    $prompt .= "2. 2-3 key health strengths they should maintain, explaining specifically why these are beneficial for their longevity\n";
    $prompt .= "3. 2-3 priority improvement areas where changes would have the biggest impact on reducing their biological age, with an explanation of why these are critical\n";
    $prompt .= "4. 3-5 specific, actionable, evidence-based recommendations tailored to their unique health profile. Each recommendation should include:\n";
    $prompt .= "   - What exact change to make\n";
    $prompt .= "   - How to implement it (specific steps)\n";
    $prompt .= "   - The expected health benefit\n";
    $prompt .= "\nFormat your response in JSON with these fields: summary, strengths (array), priorities (array), and recommendations (array).";
    
    // Set up the request to OpenAI API
    $args = array(
        'headers' => array(
            'Authorization' => 'Bearer ' . $api_key,
            'Content-Type' => 'application/json'
        ),
        'body' => json_encode(array(
            'model' => 'gpt-4o',
            'messages' => array(
                array(
                    'role' => 'system',
                    'content' => 'You are an expert health and longevity analysis assistant that provides evidence-based, personalized health insights. Analyze the user\'s health metrics carefully and provide actionable recommendations tailored to their specific situation. Focus on holistic health optimization and prioritize the most impactful interventions first. Be concise, specific, and practical in your advice.'
                ),
                array(
                    'role' => 'user',
                    'content' => $prompt
                )
            ),
            'max_tokens' => 800,
            'temperature' => 0.5
        )),
        'timeout' => 30
    );
    
    // Make the request to OpenAI
    $response = wp_remote_post('https://api.openai.com/v1/chat/completions', $args);
    
    // Check for errors
    if (is_wp_error($response)) {
        $error_message = $response->get_error_message();
        error_log('OpenAI API request failed: ' . $error_message);
        wp_send_json_error(array(
            'message' => 'Connection to AI service failed: ' . $error_message,
            'debug' => 'WP_Error occurred'
        ));
        return;
    }
    
    // Get the response code and body
    $response_code = wp_remote_retrieve_response_code($response);
    $body = wp_remote_retrieve_body($response);
    
    // Log the response for debugging
    error_log('OpenAI API response code: ' . $response_code);
    error_log('OpenAI API response body (truncated): ' . substr($body, 0, 500));
    
    // Check if the response code is not 200 OK
    if ($response_code !== 200) {
        $decoded_body = json_decode($body, true);
        $error_info = isset($decoded_body['error']) ? $decoded_body['error'] : 'Unknown API error';
        
        error_log('OpenAI API error: ' . print_r($error_info, true));
        
        // Provide specific messages for common API errors
        $error_message = 'AI service returned an error';
        if ($response_code === 401) {
            $error_message = 'Invalid API key. Please check your API key in the Longevity Settings.';
        } elseif ($response_code === 429) {
            $error_message = 'API rate limit exceeded. Please try again later.';
        } elseif ($response_code >= 500) {
            $error_message = 'AI service is currently unavailable. Please try again later.';
        }
        
        wp_send_json_error(array(
            'message' => $error_message,
            'debug' => 'HTTP ' . $response_code . ' error'
        ));
        return;
    }
    
    // Parse the response JSON
    $decoded_body = json_decode($body, true);
    
    // Check if we got a valid response
    if (!$decoded_body || !isset($decoded_body['choices']) || empty($decoded_body['choices'])) {
        wp_send_json_error(array('message' => 'Invalid response from AI service.'));
        return;
    }
    
    // Get the response content
    $ai_response = $decoded_body['choices'][0]['message']['content'];
    
    // Try to parse the JSON response
    try {
        // First, try to directly parse the response as JSON
        $analysis_results = json_decode($ai_response, true);
        
        // If direct parsing fails, try to extract JSON from the response text
        if (json_last_error() !== JSON_ERROR_NONE) {
            // Look for a JSON block in the response using regex
            preg_match('/(\{.*\})/s', $ai_response, $matches);
            
            if (!empty($matches[0])) {
                $analysis_results = json_decode($matches[0], true);
            }
        }
        
        // If we still don't have valid JSON, attempt to parse it in a different way
        if (json_last_error() !== JSON_ERROR_NONE) {
            // Fallback to manual extraction of key sections
            $analysis_results = array(
                'summary' => '',
                'strengths' => array(),
                'priorities' => array(),
                'recommendations' => array()
            );
            
            // Extract summary
            if (preg_match('/summary.*?:(.+?)(?=strengths|priorities|recommendations|\Z)/is', $ai_response, $matches)) {
                $analysis_results['summary'] = trim($matches[1]);
            }
            
            // Extract strengths
            if (preg_match('/strengths.*?:(.*?)(?=priorities|recommendations|\Z)/is', $ai_response, $matches)) {
                $strengths_text = $matches[1];
                preg_match_all('/[\-\*]\s*(.+?)(?=[\-\*]|\Z)/s', $strengths_text, $strength_matches);
                if (!empty($strength_matches[1])) {
                    $analysis_results['strengths'] = array_map('trim', $strength_matches[1]);
                }
            }
            
            // Extract priorities
            if (preg_match('/priorities.*?:(.*?)(?=recommendations|\Z)/is', $ai_response, $matches)) {
                $priorities_text = $matches[1];
                preg_match_all('/[\-\*]\s*(.+?)(?=[\-\*]|\Z)/s', $priorities_text, $priority_matches);
                if (!empty($priority_matches[1])) {
                    $analysis_results['priorities'] = array_map('trim', $priority_matches[1]);
                }
            }
            
            // Extract recommendations
            if (preg_match('/recommendations.*?:(.*?)(?=\Z)/is', $ai_response, $matches)) {
                $recommendations_text = $matches[1];
                preg_match_all('/[\-\*]\s*(.+?)(?=[\-\*]|\Z)/s', $recommendations_text, $recommendation_matches);
                if (!empty($recommendation_matches[1])) {
                    $analysis_results['recommendations'] = array_map('trim', $recommendation_matches[1]);
                }
            }
        }
        
        // Format and validate the analysis results
        $formatted_results = array(
            'summary' => isset($analysis_results['summary']) ? $analysis_results['summary'] : 'No summary available.',
            'strengths' => isset($analysis_results['strengths']) ? $analysis_results['strengths'] : array(),
            'priorities' => isset($analysis_results['priorities']) ? $analysis_results['priorities'] : array(),
            'recommendations' => isset($analysis_results['recommendations']) ? $analysis_results['recommendations'] : array()
        );
        
        // Log success
        error_log('AI analysis successfully parsed.');
        
        // Send the results back to the client
        wp_send_json_success($formatted_results);
    } catch (Exception $e) {
        error_log('Error parsing AI response: ' . $e->getMessage());
        error_log('AI response: ' . $ai_response);
        wp_send_json_error(array('message' => 'Error parsing AI response: ' . $e->getMessage()));
    }
}

// Register shortcode
function longevity_assessment_form() {
    // Ensure jQuery is loaded
    wp_enqueue_script('jquery');
    
    // Include Chart.js library
    wp_enqueue_script('chart-js', 'https://cdn.jsdelivr.net/npm/chart.js', array('jquery'), null, true);
    
    // Include Chart.js Annotation plugin
    wp_enqueue_script('chart-js-annotation', 'https://cdn.jsdelivr.net/npm/chartjs-plugin-annotation', array('chart-js'), null, true);
    
    // *** NEW: Include ZingChart library ***
    wp_enqueue_script('zingchart', 'https://cdn.zingchart.com/zingchart.min.js', array(), null, true);
    
    // Register our form's JavaScript inline
    $inline_script = "var longevity_form_data = " . json_encode(array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('longevity_form_nonce')
    )) . ";";
    
    // Print the inline script directly
    echo '<script>' . $inline_script . '</script>';

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
                    <input type="text" id="fullName" name="fullName">
                </div>
                <div class="form-group">
                    <label for="gender">Gender <span class="info-icon"><span class="tooltip">This information helps us provide more accurate health assessments and recommendations.</span></span></label>
                    <select id="gender" name="gender">
                        <option value="">Select Gender</option>
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="email">Email Address <span class="info-icon"><span class="tooltip">We'll send your assessment results and recommendations to this email address.</span></span></label>
                    <input type="email" id="email" name="email">
                </div>
                <div class="form-group">
                    <label for="practitionerEmail">Practitioner's Email <span class="info-icon"><span class="tooltip">If applicable, enter the email address of your health practitioner to share the results.</span></span></label>
                    <input type="email" id="practitionerEmail" name="practitionerEmail">
                </div>
                <div class="form-group">
                    <label for="age">Age <span class="info-icon"><span class="tooltip">Enter your current age in years.</span></span></label>
                    <input type="number" id="age" name="age" min="18" max="120">
                </div>
            </div>

            <!-- Section 2: Body Measurements -->
            <div class="form-section" id="section2">
                <h2>Body Measurements</h2>
                <div class="form-group">
                    <label for="height">Height (cm) <span class="info-icon"><span class="tooltip">Measure your height in centimeters. Stand straight against a wall with your heels together.</span></span></label>
                    <input type="number" id="height" name="height">
                </div>
                <div class="form-group">
                    <label for="weight">Weight (kg) <span class="info-icon"><span class="tooltip">Enter your current weight in kilograms. Use a digital scale for accuracy.</span></span></label>
                    <input type="number" id="weight" name="weight">
                </div>
                <div class="form-group">
                    <label for="waist">Waist Circumference (cm) <span class="info-icon"><span class="tooltip">Measure around your waist at the level of your belly button. Keep the tape measure horizontal.</span></span></label>
                    <input type="number" id="waist" name="waist">
                </div>
                <div class="form-group">
                    <label for="hip">Hip Circumference (cm) <span class="info-icon"><span class="tooltip">Measure around the widest part of your hips. Keep the tape measure horizontal.</span></span></label>
                    <input type="number" id="hip" name="hip">
                </div>
                <div class="form-group">
                    <label for="overallHealthPercent">Overall Health Percentage (%) <span class="info-icon"><span class="tooltip">If you have completed a separate health assessment that provided an overall health score (as a percentage), please enter that score here.</span></span></label>
                    <input type="number" id="overallHealthPercent" name="overallHealthPercent" min="0" max="100" step="1">
                </div>
            </div>

            <!-- Section 3: Lifestyle Factors -->
            <div class="form-section" id="section3">
                <h2>Lifestyle Factors</h2>
                <div class="form-group">
                    <label for="activity">Physical Activity Level <span class="info-icon"><span class="tooltip">Select the option that best describes your typical weekly physical activity. Consider the type, frequency and intensity of your exercise and daily movement.</span></span></label>
                    <select id="activity" name="activity">
                        <option value="">Select Activity Level</option>
                        <option value="0">Sedentary (minimal activity)</option>
                        <option value="1">Very low (occasional walking)</option>
                        <option value="2">Low (regular walking or light activity)</option>
                        <option value="3" selected>Moderate (regular moderate exercise)</option>
                        <option value="4">High (structured exercise 3+ times/week)</option>
                        <option value="5">Very high (intense training 4+ times/week)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="sleepDuration">Sleep Duration <span class="info-icon"><span class="tooltip">Choose the sleep duration that most closely matches your average nightly sleep over the past month. Base your answer on a typical night rather than an occasional variation.</span></span></label>
                    <select id="sleepDuration" name="sleepDuration">
                        <option value="">Select Sleep Duration</option>
                        <option value="0">Less than 4 hours (severely insufficient)</option>
                        <option value="1">4–5 hours (very short sleep)</option>
                        <option value="2">5–6 hours (short sleep)</option>
                        <option value="3" selected>6–7 hours (slightly below average)</option>
                        <option value="4">7–8 hours (recommended duration)</option>
                        <option value="5">More than 8 hours (extended sleep)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="sleepQuality">Sleep Quality <span class="info-icon"><span class="tooltip">Rate how frequently you experience restful, uninterrupted sleep. Consider factors such as feeling refreshed in the morning and the number of awakenings during the night.</span></span></label>
                    <select id="sleepQuality" name="sleepQuality">
                        <option value="">Select Sleep Quality</option>
                        <option value="0">Never (I never sleep well)</option>
                        <option value="1">Rarely (seldom restful sleep)</option>
                        <option value="2">Occasionally (inconsistent quality)</option>
                        <option value="3" selected>Sometimes (moderate quality sleep)</option>
                        <option value="4">Often (mostly restful sleep)</option>
                        <option value="5">Always (consistently high quality sleep)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="stressLevels">Stress Levels <span class="info-icon"><span class="tooltip">Indicate how often you feel stressed or overwhelmed. Reflect on both work and personal life to determine your usual stress level.</span></span></label>
                    <select id="stressLevels" name="stressLevels">
                        <option value="">Select Stress Level</option>
                        <option value="0">Constantly stressed (high anxiety, unrelenting)</option>
                        <option value="1">Very high stress (frequent overwhelming stress)</option>
                        <option value="2">Moderate stress (often challenging to manage)</option>
                        <option value="3" selected>Sometimes stressed (occasional stress episodes)</option>
                        <option value="4">Low stress (generally calm)</option>
                        <option value="5">Rarely stressed (minimal stress, very relaxed)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="socialConnections">Social Connections <span class="info-icon"><span class="tooltip">Select the option that best represents the frequency of your social interactions, including contact with friends, family and community groups.</span></span></label>
                    <select id="socialConnections" name="socialConnections">
                        <option value="">Select Social Connection Level</option>
                        <option value="0">None (no regular social interaction)</option>
                        <option value="1">Rarely (infrequent social contact)</option>
                        <option value="2">Occasionally (sporadic interaction with friends/family)</option>
                        <option value="3" selected>Regularly (consistent weekly social contact)</option>
                        <option value="4">Often (frequent social engagement)</option>
                        <option value="5">Daily (social interactions every day)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="dietQuality">Diet Quality <span class="info-icon"><span class="tooltip">Select the option that best describes the overall quality of your regular diet. Think about the variety, nutrient density and frequency of processed foods in your meals.</span></span></label>
                    <select id="dietQuality" name="dietQuality">
                        <option value="">Select Diet Quality</option>
                        <option value="0">Very poor (nutrient deficient, unhealthy choices)</option>
                        <option value="1">Poor (limited variety, low nutrient density)</option>
                        <option value="2">Below average (occasional healthy meals, frequent unhealthy choices)</option>
                        <option value="3" selected>Average (balanced diet with some healthy choices)</option>
                        <option value="4">Good (mostly nutritious and balanced)</option>
                        <option value="5">Excellent (high nutrient density, varied and balanced)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="alcoholConsumption">Alcohol Consumption <span class="info-icon"><span class="tooltip">Select your average weekly alcohol consumption. Base your answer on standard drink sizes as defined in your region.</span></span></label>
                    <select id="alcoholConsumption" name="alcoholConsumption">
                        <option value="">Select Alcohol Consumption</option>
                        <option value="0">15+ drinks per week (heavy drinking)</option>
                        <option value="1">10-14 drinks per week (frequent heavy drinking)</option>
                        <option value="2">6-9 drinks per week (moderate consumption)</option>
                        <option value="3" selected>3-5 drinks per week (light to moderate consumption)</option>
                        <option value="4">1-2 drinks per week (occasional drinking)</option>
                        <option value="5">0 drinks (abstainer)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="smokingStatus">Smoking Status <span class="info-icon"><span class="tooltip">Choose the option that best describes your current or past smoking habits. Consider both the frequency and recency of your smoking behaviour.</span></span></label>
                    <select id="smokingStatus" name="smokingStatus">
                        <option value="">Select Smoking Status</option>
                        <option value="0">Current daily smoker (smokes every day)</option>
                        <option value="1">Regular smoker (smokes on most days)</option>
                        <option value="2">Occasional smoker (smokes infrequently)</option>
                        <option value="3" selected>Recently quit (stopped smoking within the last 6 months)</option>
                        <option value="4">Former smoker (quit more than 6 months ago)</option>
                        <option value="5">Never smoked (no history of smoking)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="cognitiveActivity">Cognitive Activity <span class="info-icon"><span class="tooltip">Select how often you engage in activities that challenge your brain. This can include puzzles, reading, learning new skills or other mentally stimulating tasks.</span></span></label>
                    <select id="cognitiveActivity" name="cognitiveActivity">
                        <option value="">Select Cognitive Activity Level</option>
                        <option value="0">Never (no cognitive activities, e.g. puzzles or reading)</option>
                        <option value="1">Rarely (infrequent mental stimulation)</option>
                        <option value="2">Occasionally (sporadic cognitive activities)</option>
                        <option value="3" selected>Regularly (weekly engagement in brain-stimulating tasks)</option>
                        <option value="4">Frequently (almost daily mental stimulation)</option>
                        <option value="5">Daily (consistent daily mental exercises such as puzzles or reading)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="sunlightExposure">Sunlight Exposure <span class="info-icon"><span class="tooltip">Choose the duration of natural sunlight you typically receive daily. Consider exposure during morning or afternoon hours, not just while indoors.</span></span></label>
                    <select id="sunlightExposure" name="sunlightExposure">
                        <option value="">Select Sunlight Exposure</option>
                        <option value="0">Less than 10 minutes (minimal exposure)</option>
                        <option value="1">10-20 minutes (brief exposure)</option>
                        <option value="2">20-30 minutes (short daily exposure)</option>
                        <option value="3" selected>30-60 minutes (moderate daily exposure)</option>
                        <option value="4">1-2 hours (extended daily exposure)</option>
                        <option value="5">More than 2 hours (high exposure)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="supplementIntake">Supplement Intake <span class="info-icon"><span class="tooltip">Indicate how often you take dietary supplements such as vitamins or minerals. Reflect on your routine over the past month.</span></span></label>
                    <select id="supplementIntake" name="supplementIntake">
                        <option value="">Select Supplement Intake</option>
                        <option value="0">None (no supplements)</option>
                        <option value="1">Rarely (less than once per week)</option>
                        <option value="2">Occasionally (1-2 times per week)</option>
                        <option value="3" selected>Regularly (3-4 times per week)</option>
                        <option value="4">Frequently (5-6 times per week)</option>
                        <option value="5">Daily (every day)</option>
                    </select>
                </div>
            </div>

            <!-- Section 4: Physical Performance Metrics -->
            <div class="form-section" id="section4">
                <h2>Physical Performance Metrics</h2>
                <div class="form-group">
                    <label for="sitStand">Sit-to-Stand Test <span class="info-icon"><span class="tooltip">Record the number of complete sit-to-stand repetitions you can perform in 30 seconds using a standard chair. Ensure you count only full, proper repetitions.</span></span></label>
                    <select id="sitStand" name="sitStand">
                        <option value="">Select Capability Level</option>
                        <option value="0">0 points (unable to perform any stand-ups)</option>
                        <option value="1">1-2 points (minimal performance)</option>
                        <option value="2">3-5 points (below average performance)</option>
                        <option value="3" selected>6-7 points (average performance)</option>
                        <option value="4">8-9 points (above average performance)</option>
                        <option value="5">10 points (excellent performance)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="breathHold">Breath Hold Test <span class="info-icon"><span class="tooltip">Measure the time (in seconds) you can hold your breath after a normal exhalation. Use a stopwatch and take your best, safe attempt.</span></span></label>
                    <select id="breathHold" name="breathHold">
                        <option value="">Select Breath Hold Duration</option>
                        <option value="0">Less than 15 seconds (very short duration)</option>
                        <option value="1">15-29 seconds (short duration)</option>
                        <option value="2">30-45 seconds (moderate duration)</option>
                        <option value="3" selected>46-60 seconds (good duration)</option>
                        <option value="4">61-90 seconds (long duration)</option>
                        <option value="5">More than 90 seconds (very long duration)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="balance">Balance Test <span class="info-icon"><span class="tooltip">Record how long (in seconds) you can maintain balance on one leg without support. Use a stopwatch and repeat if needed to confirm your best time.</span></span></label>
                    <select id="balance" name="balance">
                        <option value="">Select Balance Level</option>
                        <option value="0">Less than 10 seconds (poor balance)</option>
                        <option value="1">10-19 seconds (below average balance)</option>
                        <option value="2">20-29 seconds (moderate balance)</option>
                        <option value="3" selected>30-39 seconds (good balance)</option>
                        <option value="4">40-59 seconds (very good balance)</option>
                        <option value="5">More than 60 seconds (excellent balance)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="skinElasticity">Skin Elasticity <span class="info-icon"><span class="tooltip">After gently pinching and releasing your skin (e.g. on the back of your hand), record the time in seconds it takes to return to normal. Use a stopwatch for accuracy.</span></span></label>
                    <select id="skinElasticity" name="skinElasticity">
                        <option value="">Select Skin Elasticity Level</option>
                        <option value="0">More than 30 seconds (very low elasticity)</option>
                        <option value="1">16-30 seconds (low elasticity)</option>
                        <option value="2">10-15 seconds (below average elasticity)</option>
                        <option value="3" selected>5-9 seconds (moderate elasticity)</option>
                        <option value="4">3-4 seconds (good elasticity)</option>
                        <option value="5">1-2 seconds (excellent elasticity)</option>
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

                    <!-- Row 2: Aging Rate - REPLACED WITH ZINGCHART -->
                    <div class="result-card full-width-card" id="agingRateCard">
                        <h3>Aging Rate</h3>
                         <!-- ZingChart will display the value and interpretation -->
                         <!-- *** NEW: Container for ZingChart Gauge *** -->
                         <div id="zingChartAgingRateGaugeContainer" style="width:100%; min-height:300px;"></div>
                         <!-- *** REMOVED old agingRateDisplay and SVG wrapper *** -->
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
                     <!-- --- End Age Impact Factors Section --- -->

                     <!-- --- AI Analysis Section --- -->
                     <div class="full-width-section" id="aiAnalysisSection">
                         <h3>AI Personalized Analysis</h3>
                         <div class="ai-analysis-container">
                             <div class="ai-status">
                                 <div class="ai-loading">
                                     <span class="material-icons ai-loading-icon">autorenew</span>
                                     <p>Analyzing your data...</p>
                                 </div>
                             </div>
                             <div class="ai-content" style="display: none;">
                                 <div class="ai-header">
                                     <span class="material-icons ai-icon">psychology</span>
                                     <div class="ai-branding">
                                         <h4>AI Health Insights</h4>
                                         <p class="ai-subtitle">Personalized analysis based on your assessment</p>
                                     </div>
                                 </div>
                                 <div class="ai-sections">
                                     <div class="ai-section">
                                         <h5>Summary</h5>
                                         <div class="ai-summary"></div>
                                     </div>
                                     <div class="ai-columns">
                                         <div class="ai-column">
                                             <h5>Key Strengths</h5>
                                             <div class="ai-strengths"></div>
                                         </div>
                                         <div class="ai-column">
                                             <h5>Priority Areas</h5>
                                             <div class="ai-priorities"></div>
                                         </div>
                                     </div>
                                     <div class="ai-section">
                                         <h5>Personalized Recommendations</h5>
                                         <div class="ai-recommendations"></div>
                                     </div>
                                 </div>
                             </div>
                         </div>
                     </div>
                     <!-- --- End AI Analysis Section --- -->

                     <!-- Section: Detailed Breakdown (Full Width) -->
                     <div class="full-width-section" id="detailedBreakdownSection">
                         <h3>Detailed Breakdown</h3>
                         <!-- Populated by JS: Shows scores for each metric -->
                         <div id="detailedBreakdown"></div> <!-- Existing content div -->
                     </div>
                </div>
            </div>
        </form>
    </div>

    <style>
        /* Premium Form Styling following Apple Design Guidelines */
        .longevity-form-container {
            max-width: 800px;
            margin: 3rem auto; /* Adjusted margin for slightly more space top/bottom */
            padding: 2.5rem 2rem; /* Adjusted padding */
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            color: #1d1d1f;
            border: 1px solid #e5e5e5;
            border-radius: 16px;
            background-color: #fdfdfd; /* Slightly off-white background */
        }

        /* Form Sections */
        .form-section {
            padding: 2.5rem;
            margin-bottom: 2rem; /* Consistent bottom margin for sections */
            border-radius: 16px; /* Existing */
            /* Removed background/border from individual sections for a cleaner look within the main container */
        }

        .form-section h2 {
            color: #1d1d1f;
            margin-bottom: 2.5rem; /* Increased space below section titles */
            font-size: 1.75rem;
            font-weight: 600;
            letter-spacing: -0.02em;
            text-align: left; /* Ensure alignment */
        }

        .form-group {
            margin-bottom: 1.75rem; /* Slightly adjusted spacing between form fields */
            position: relative; /* Keep for tooltip positioning */
        }

        label {
            display: block;
            margin-bottom: 0.75rem; /* Consistent space below label */
            font-weight: 500;
            color: #1d1d1f;
            font-size: 1rem; /* Meets 11pt minimum */
            letter-spacing: -0.01em;
        }

        input, select {
            width: 100%;
            padding: 14px 16px;
            border: 1px solid #d1d1d6; /* Slightly softer border color */
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.2s ease;
            background-color: #ffffff;
            color: #1d1d1f;
            min-height: 44px; /* Apple's minimum touch target size */
            box-sizing: border-box; /* Ensure padding doesn't increase size */
        }

        input:focus, select:focus {
            outline: none;
            border-color: #007AFF;
            box-shadow: 0 0 0 3px rgba(0,122,255,0.15); /* Adjusted focus shadow */
        }

        select {
            appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%238e8e93' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e"); /* Subtler chevron color */
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
            padding-top: 1rem; /* Add some space above the button */
        }

        .submit-btn {
            padding: 16px 32px; /* Slightly adjusted padding */
            border-radius: 12px;
            border: none;
            font-size: 1.05rem; /* Adjusted font size */
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            background: #007AFF;
            color: white;
            min-width: 220px; /* Adjusted min-width */
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
            transform: translateY(-50%) translateX(12px); /* Adjusted transform for spacing */
            background: rgba(29, 29, 31, 0.95); /* Slightly transparent dark background */
            color: white;
            padding: 10px 14px; /* Adjusted padding */
            border-radius: 8px;
            font-size: 0.875rem; /* Ensure this meets ~11pt legibility */
            line-height: 1.4; /* Improved line spacing */
            width: 240px;
            z-index: 100;
            opacity: 0;
            transition: opacity 0.2s ease, transform 0.2s ease;
            margin-left: 0; /* Removed margin, using transform now */
            box-shadow: 0 4px 12px rgba(0,0,0,0.15); /* Slightly stronger shadow */
            pointer-events: none; /* Prevent tooltip from blocking interaction */
        }

        .info-icon:hover .tooltip {
            visibility: visible;
            opacity: 1;
            transform: translateY(-50%) translateX(16px); /* Move slightly further on hover */
        }

        .tooltip::before {
            content: "";
            position: absolute;
            left: -5px; /* Adjusted arrow position */
            top: 50%;
            transform: translateY(-50%);
            border-width: 6px 6px 6px 0;
            border-style: solid;
            border-color: transparent rgba(29, 29, 31, 0.95) transparent transparent; /* Match background */
        }

        @media (max-width: 768px) {
            .tooltip {
                left: 0;
                top: calc(100% + 8px); /* Position below the icon with margin */
                transform: translateX(0); /* Reset horizontal transform */
                margin-left: 0;
                margin-top: 0; /* Reset margin-top */
                width: calc(100% - 10px); /* Adjust width for small screens */
                transform: translateX(5px); /* Center slightly */
            }

            .info-icon:hover .tooltip {
                 transform: translateX(5px); /* Keep transform consistent on hover */
            }

            .tooltip::before {
                left: 20px;
                top: -6px;
                transform: translateX(0); /* Reset transform */
                border-width: 0 6px 6px 6px;
                border-color: transparent transparent rgba(29, 29, 31, 0.95) transparent; /* Match background */
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
             margin-bottom: 2rem; /* Increased space below title */
             font-size: 1.35rem; /* Adjusted size */
             font-weight: 600;
             letter-spacing: -0.02em;
             text-align: center; /* Center title like Image 1 */
             border-bottom: 1px solid #e5e5e5; /* Add a separator */
             padding-bottom: 1rem;
         }
         .full-width-section h3::before {
             display: none; /* Remove the blue line from card titles */
             background: #007AFF;
             border-radius: 2px;
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
            font-size: 2.25rem; /* Slightly smaller */
            font-weight: 700;
            color: #007AFF;
            margin: 0.5rem 0; /* Reduced margin */
            text-align: center;
            line-height: 1.2;
        }

        .metric-label {
            color: #666;
            font-size: 0.95rem; /* Increased from 0.85rem */
            text-align: center;
            margin-bottom: 1rem; /* Reduced margin */
        }
        
        /* Classes for age shift color coding */
        .age-shift-value {
            font-weight: 500; /* Slightly bolder */
        }
        .age-shift-positive {
            color: #e64c3c; /* Subtle red */
        }
        .age-shift-negative {
            color: #27ae60; /* Subtle green */
        }

        .score-bar {
            height: 8px;
            /* background: #f5f5f5; */ /* Keep overridden value below */
            /* border-radius: 4px; */ /* Keep overridden value below */
            margin: 1rem 0;
            overflow: hidden;
            /* padding: 1rem; */ /* Removed padding causing extra space */
            background: #f8f8f8;
            border-radius: 12px;
            box-shadow: inset 0 1px 2px rgba(0,0,0,0.04); /* Subtle inner shadow */
        }

        .score-fill {
            height: 100%;
            background: #007AFF;
            transition: width 0.5s ease;
            border-radius: 12px; /* Added matching border-radius */
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
            flex: 1; /* Allow flex items to grow/shrink */
        }

        .age-value .value {
            font-size: 1.8rem; /* Adjusted size */
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
                gap: 1.5rem; /* Reduced gap on mobile */
            }

            .metric-value {
                font-size: 2rem;
            }

            .age-comparison {
                flex-direction: column;
                gap: 1rem;
                padding: 0.8rem; /* Adjusted padding */
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
            align-items: baseline; /* Align baselines */
            letter-spacing: -0.01em; /* Apple-like tight letter spacing */
        }

        .gauge-container {
            position: relative;
            width: 100%;
            margin-bottom: 8px; /* Less space below gauge */
            padding: 8px 0; /* Add padding above/below for marker visibility */
        }

        .gauge-bar {
            width: 100%;
            height: 10px; /* Slightly thinner but still visible */
            border-radius: 20px; /* More rounded ends */
            /* Adjusted gradient to align with BMI categories on the 15-40 scale */
            /* BMI scale points at: 15, 18.5, 25, 30, 40 */
            /* For a 15-40 scale (25 point range), percentages are: 15=0%, 18.5=14%, 25=40%, 30=60%, 40=100% */
            background: linear-gradient(to right,
                #FF3B30 0%, #FF3B30 14%, /* Red (Underweight <18.5) */
                #34C759 14%, #34C759 40%, /* Green (Healthy 18.5-24.9) */
                #FF9500 40%, #FF9500 60%, /* Orange (Overweight 25-29.9) */
                #FF3B30 60%, #FF3B30 100% /* Red (Obese ≥30) */
            );
            box-shadow: 0 1px 2px rgba(0,0,0,0.08);
        }

        .gauge-marker {
            position: absolute;
            top: 7px;
            left: 50%;
            transform: translateX(-50%);
            width: 0;
            height: 0;
            border-left: 6px solid transparent;
            border-right: 6px solid transparent;
            border-top: 8px solid #000; /* Black for high contrast */
            z-index: 2;
            filter: drop-shadow(0 1px 1px rgba(0,0,0,0.2));
        }

        /* Add a small white dot at the gauge-marker intersection with bar for better visibility */
        .gauge-marker::after {
            content: '';
            position: absolute;
            width: 4px;
            height: 4px;
            background: white;
            border-radius: 50%;
            top: -10px; /* Position above the triangle */
            left: 50%;
            transform: translateX(-50%);
            box-shadow: 0 0 2px rgba(0,0,0,0.3);
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

        /* Improve the Body Composition section container with Apple-like styling */
        #bodyMeasurementsSection {
            padding: 2rem 1.5rem; /* Adjusted padding */
            margin-top: 2rem;
            margin-bottom: 2rem;
            background: #f9f9f9; /* Simpler light background */
            border-radius: 16px; /* More rounded corners */
            box-shadow: none; /* Removed shadow for flatter design */
            border: 1px solid #e5e5e5; /* Add subtle border */
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            font-family: -apple-system, BlinkMacSystemFont, "SF Pro Text", "San Francisco", "Helvetica Neue", sans-serif; /* Apple system fonts */
        }

        #bodyMeasurementsSection:hover {
            transform: translateY(-2px);
            /* Keep shadow subtle on hover */
            box-shadow: 0 4px 15px rgba(0,0,0,0.06);
        }

        #bodyMeasurementsSection h3 {
            color: #1d1d1f; /* Apple-like dark gray */
            margin-bottom: 2rem; /* Increased space */
            font-weight: 600;
            letter-spacing: -0.01em; /* Slightly tighter letter spacing */
            font-size: 1.5rem;
            text-align: center;
            border-bottom: none; /* Removed border for cleaner title */
            padding-bottom: 0; /* Removed padding */
        }

        #bodyMeasurements {
            max-width: 700px; /* Limit width for better readability */
            margin: 0 auto; /* Center content */
            padding: 0 0.5rem; /* Add slight horizontal padding for content */
        }

        /* Enhanced gauge styling with Apple design cues */
        .gauge-outer {
            background: #ffffff;
            border-radius: 12px;
            padding: 18px 20px; /* Adjusted padding */
            box-shadow: 0 2px 6px rgba(0,0,0,0.04); /* Adjusted shadow */
            margin-bottom: 1.5rem; /* Adjusted spacing */
            border: 1px solid #e5e5e5; /* Consistent border */
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            animation: fadeInUp 0.6s ease forwards;
            opacity: 0; /* Start invisible and fade in */
        }

        /* Set different animation delays for second gauge to create staggered effect */
        .gauge-outer:nth-child(2) {
            animation-delay: 0.2s;
        }

        .gauge-outer:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.07);
        }

        .gauge-metric {
            margin-bottom: 20px;
            position: relative;
        }

        .gauge-label {
            font-size: 1.05em; /* Adjusted size */
            color: #1d1d1f;
            margin-bottom: 12px;
            font-weight: 600;
            display: flex;
            justify-content: space-between;
            align-items: baseline; /* Align baselines */
            letter-spacing: -0.01em; /* Apple-like tight letter spacing */
        }

        /* Style the gauge category/risk level information */
        .gauge-metric .gauge-category {
            font-weight: 500;
            color: #666;
            letter-spacing: 0;
            font-size: 0.85em; /* Adjusted size */
        }

        .gauge-container {
            position: relative;
            width: 100%;
            margin-bottom: 6px; /* Reduced padding */
        }

        .gauge-bar {
            width: 100%;
            height: 10px; /* Slightly thinner but still visible */
            border-radius: 20px; /* More rounded ends */
            /* Smoother color gradient with Apple-like colors */
            background: linear-gradient(to right,
                #FF3B30 0%, #FF3B30 16.66%, /* Apple red */
                #FF9500 16.66%, #FF9500 33.33%, /* Apple orange */
                #34C759 33.33%, #34C759 66.66%, /* Apple green */
                #FF9500 66.66%, #FF9500 83.33%, /* Apple orange */
                #FF3B30 83.33%, #FF3B30 100% /* Apple red */
            );
            box-shadow: 0 1px 2px rgba(0,0,0,0.08);
        }

        .gauge-marker {
            position: absolute;
            top: 7px;
            left: 50%;
            transform: translateX(-50%);
            width: 0;
            height: 0;
            border-left: 6px solid transparent;
            border-right: 6px solid transparent;
            border-top: 8px solid #000; /* Black for high contrast */
            z-index: 2;
            filter: drop-shadow(0 1px 1px rgba(0,0,0,0.2));
        }

        /* Improved scale markers */
        .gauge-scale {
            display: flex;
            justify-content: space-between;
            margin-top: 4px;
            padding: 0 4px; /* Adjusted padding */
            font-size: 0.75em;
            color: #86868b; /* Apple secondary text color */
            font-weight: 500;
        }

        .gauge-interpretation {
            font-size: 0.85em;
            color: #86868b; /* Apple secondary text color */
            margin-top: 12px;
            font-weight: 400;
            line-height: 1.4; /* Improved line height for better readability */
            letter-spacing: -0.01em;
            padding: 0 2px; /* Add slight padding for text alignment */
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
            box-shadow: 0 2px 6px rgba(0,0,0,0.04); /* Adjusted shadow */
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            border: 1px solid #e5e5e5; /* Consistent border */
        }
        
        .impact-column:first-child {
             background-color: rgba(39, 174, 96, 0.08); /* Light Green */
        }
        
        .impact-column:last-child {
             background-color: rgba(231, 76, 60, 0.08); /* Light Red */
        }

        .impact-column:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0,0,0,0.07); /* Adjusted hover shadow */
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
            padding: 14px 0; /* Increased vertical padding */
            border-bottom: 1px solid #f5f5f5;
            min-height: 44px; /* Apple's minimum touch target size */
            align-items: flex-start;
            transition: background-color 0.15s ease;
            position: relative; /* For potential future absolute elements if needed */
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
            margin-right: 14px; /* Increased spacing */
            color: #86868b; /* Softer icon color */
        }

        .factor-content {
            flex: 1;
            padding-right: 5px; /* Ensure space for impact value */
        }

        .factor-name {
            font-weight: 600;
            font-size: 1rem; /* Slightly larger */
            color: #1d1d1f;
            margin-bottom: 6px;
            display: flex;
            justify-content: space-between;
            align-items: baseline; /* Keep space-between */
            flex-wrap: wrap; /* Allow wrapping if name is long */
        }

        .factor-impact {
            font-weight: 700;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 0.8rem; /* Slightly smaller impact text */
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
            margin-left: 8px; /* Add space between name and impact */
            white-space: nowrap; /* Prevent impact value wrapping */
            flex-shrink: 0; /* Prevent shrinking */
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
            color: #86868b; /* Consistent secondary text color */
            line-height: 1.5;
        }

        #ageImpactSection {
            background: #f9f9f9; /* Consistent light background */
            border-radius: 16px;
            padding: 2rem;
            margin: 2rem 0;
            box-shadow: none; /* Remove shadow for flatter look */
            border: 1px solid #e5e5e5; /* Add border */
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

        /* --- AI Analysis Section --- */
        .ai-analysis-container {
            background: #ffffff;
            border-radius: 12px; /* Slightly less rounded */
            padding: 2rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            border: 1px solid #e5e5e5;
            margin-top: 1rem; /* Add space below section title */
        }

        .ai-status {
            /* Combined loading/error/content display logic, so status div might not be needed */
            display: none; /* Hide if not actively used */
        }

        .ai-loading {
            display: flex;
            align-items: center;
            justify-content: center; /* Center loading indicator */
            gap: 1rem;
            padding: 2rem 0; /* Add padding when loading */
            color: #86868b;
        }

        .ai-loading-icon {
            font-size: 1.8rem; /* Slightly smaller */
            color: #007AFF;
        }

        .ai-content {
            display: none; /* Keep hidden initially */
        }

        .ai-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .ai-icon {
            font-size: 2rem;
            color: #007AFF;
        }

        .ai-branding {
            flex: 1;
        }

        .ai-subtitle {
            font-size: 1rem;
            color: #666;
        }

        .ai-sections {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .ai-section {
            background: #f9f9f9; /* Consistent light background */
            border-radius: 10px;
            padding: 1rem;
            box-shadow: none; /* Remove inner shadow */
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            border: 1px solid #ededed; /* Softer border */
        }

        .ai-section:hover {
            transform: none; /* Remove hover effect */
            box-shadow: none;
        }

        .ai-columns {
            display: flex;
            gap: 1rem; /* Add gap between columns */
            justify-content: space-between;
        }

        .ai-column {
            flex: 1;
            background: #f9f9f9; /* Consistent light background */
            border-radius: 10px;
            padding: 1.25rem; /* Adjusted padding */
            box-shadow: none; /* Remove inner shadow */
            transition: none; /* Remove hover effect */
            border: 1px solid #ededed; /* Softer border */
        }

        .ai-column:hover {
            transform: none;
            box-shadow: none;
        }

        .ai-summary, .ai-strengths, .ai-priorities, .ai-recommendations {
            /* These are now divs within ai-section or ai-column, remove extra styling */
            padding: 0;
            border-radius: 0;
            background: none;
            box-shadow: none;
            transition: none;
            border: none;
        }

        .ai-summary:hover, .ai-strengths:hover, .ai-priorities:hover, .ai-recommendations:hover {
            transform: none;
            box-shadow: none;
        }

        .ai-section h5, .ai-column h5 {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 0.75rem; /* Reduced margin below sub-headers */
            color: #1d1d1f; /* Ensure consistent header color */
        }

        .ai-summary p,
        .ai-strengths ul, .ai-priorities ul, .ai-recommendations ul {
            font-size: 0.95rem; /* Slightly larger text */
            color: #3c3c43; /* Slightly darker secondary text */
            line-height: 1.5;
            margin: 0; /* Reset margin */
            padding-left: 0; /* Reset padding for lists */
            list-style-position: inside; /* Keep bullets inside */
        }

        .ai-strengths ul li, .ai-priorities ul li, .ai-recommendations ul li {
            margin-bottom: 0.5rem; /* Space between list items */
        }

        /* Remove redundant styling inherited from general result elements */
        .ai-summary .metric-value, .ai-strengths .metric-value, .ai-priorities .metric-value, .ai-recommendations .metric-value,
        .ai-summary .metric-label, .ai-strengths .metric-label, .ai-priorities .metric-label, .ai-recommendations .metric-label,
        .ai-summary .score-bar, .ai-strengths .score-bar, .ai-priorities .score-bar, .ai-recommendations .score-bar,
        .ai-summary .score-fill, .ai-strengths .score-fill, .ai-priorities .score-fill, .ai-recommendations .score-fill,
        .ai-summary .breakdown-item, .ai-strengths .breakdown-item, .ai-priorities .breakdown-item, .ai-recommendations .breakdown-item,
        .ai-summary .breakdown-label, .ai-strengths .breakdown-label, .ai-priorities .breakdown-label, .ai-recommendations .breakdown-label,
        .ai-summary .breakdown-value, .ai-strengths .breakdown-value, .ai-priorities .breakdown-value, .ai-recommendations .breakdown-value,
        .ai-summary .age-comparison, .ai-strengths .age-comparison, .ai-priorities .age-comparison, .ai-recommendations .age-comparison,
        .ai-summary .age-value, .ai-strengths .age-value, .ai-priorities .age-value, .ai-recommendations .age-value,
        .ai-summary .age-value .value, .ai-strengths .age-value .value, .ai-priorities .age-value .value, .ai-recommendations .age-value .value,
        .ai-summary .age-value .label, .ai-strengths .age-value .label, .ai-priorities .age-value .label, .ai-recommendations .age-value .label,
        .ai-summary .age-difference, .ai-strengths .age-difference, .ai-priorities .age-difference, .ai-recommendations .age-difference {
           /* Remove potentially conflicting styles if not used directly in AI section */
           /* If any ARE used, style them specifically within .ai-* context */
           all: unset; /* Reset inherited styles - use with caution */
           /* Re-apply necessary display styles if needed */
           display: block; /* Or inline, flex etc. as needed */
        }

        @media (max-width: 768px) {
            .ai-columns {
                flex-direction: column;
                gap: 1rem;
            }

            .ai-column {
                width: 100%;
            }

            .ai-summary .metric-value, .ai-strengths .metric-value, .ai-priorities .metric-value, .ai-recommendations .metric-value {
                font-size: 2rem;
            }

            .ai-summary .age-comparison, .ai-strengths .age-comparison, .ai-priorities .age-comparison, .ai-recommendations .age-comparison {
                flex-direction: column;
                gap: 1rem;
            }
        }
        /* --- End AI Analysis Section --- */

        /* --- Detailed Breakdown Chart Section --- */
        .detailed-breakdown-toggle {
            display: flex;
            justify-content: center;
            margin-bottom: 1.5rem;
        }

        .segmented-control {
            display: inline-flex;
            background-color: #e9e9eb; /* Lighter gray background */
            border-radius: 9px; /* Apple standard corner radius */
            padding: 3px;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
        }

        .segmented-control button {
            padding: 8px 20px;
            margin: 0;
            border: none;
            background-color: transparent;
            color: #1d1d1f;
            font-size: 13px; /* Slightly smaller than labels, typical for controls */
            font-weight: 500;
            cursor: pointer; /* Existing */
            border-radius: 7px; /* Slightly less than container - Existing */
            transition: background-color 0.2s ease, color 0.2s ease, box-shadow 0.2s ease; /* Existing */
            min-height: 36px; /* Increased min-height slightly */
            line-height: 1.4; /* Adjusted line height */
            display: inline-flex; /* Ensure flex alignment works */
            align-items: center; /* Vertically center text */
        }

        .segmented-control button.active {
            background-color: #ffffff;
            color: #1d1d1f;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .segmented-control button:not(.active):hover {
            background-color: rgba(0, 0, 0, 0.05); /* Subtle hover effect */
        }

        .chart-container {
            background: #ffffff;
            border-radius: 16px;
            padding: 24px; /* Adjusted padding */
            margin: 24px auto;
            box-shadow: 0 2px 6px rgba(0,0,0,0.04); /* Adjusted shadow */
            border: 1px solid #e5e5e5; /* Add border */
            transition: opacity 0.3s ease, transform 0.3s ease;
            min-height: 400px; /* Adjusted min height */
            width: 95%; /* Made slightly wider */
            max-width: 750px; /* Increased max width */
        }

        .chart-container.hidden {
            display: none; /* Initially hide */
            opacity: 0;
            transform: translateY(10px);
        }
        .chart-container.visible {
            display: block; /* Make visible */
            opacity: 1;
            transform: translateY(0);
        }

        .chart-title {
            color: #1d1d1f;
            font-size: 1.25rem; /* Larger title */
            font-weight: 600; /* Existing */
            text-align: center;
            margin-bottom: 24px; /* Adjusted margin */
            padding-bottom: 0; /* Remove padding */
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            border-bottom: none; /* Removed border */
        }
        /* --- End Detailed Breakdown Chart Section --- */

        /* Ensure chart canvas is responsive */
        canvas {
            max-width: 100%;
            height: auto !important; /* Override Chart.js inline style */
        }
        
        /* Chart fallback for printing */
        .chart-fallback {
            display: none;
        }
        
        .chart-print-image {
            max-width: 100%;
            height: auto;
        }
        
        @media print {
            .chart-fallback {
                display: block;
                page-break-inside: avoid;
                margin: 20px auto;
                text-align: center;
            }
            
            canvas {
                display: none !important;
            }
            
            .chart-container {
                box-shadow: none;
                border: 1px solid #eee;
                page-break-inside: avoid;
            }
            
            .noprint, 
            .detailed-breakdown-toggle, 
            .segmented-control {
                display: none !important;
            }
        }

        /* Responsive adjustments for smaller screens */
        @media (max-width: 768px) {
            .chart-container {
                padding: 20px;
                min-height: 400px;
                width: 95%;
            }
            
            .chart-title {
                font-size: 1.1rem;
                margin-bottom: 20px;
            }
        }

        @media (max-width: 480px) {
            .chart-container {
                padding: 15px;
                min-height: 350px;
            }
            
            /* Adjust segmented control for smaller screens */
            .segmented-control button {
                padding: 6px 15px;
                font-size: 12px;
            }
        }

        /* Add fade-in animation for gauges and ensure San Francisco font family */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Add subtle tick marks to the gauge bars for better visual precision */
        .gauge-bar::before {
            content: '';
            position: absolute;
            top: 0;
            left: 50%;
            height: 14px; /* Slightly taller than the bar */
            width: 1px;
            background-color: rgba(255,255,255,0.7);
            transform: translateX(-50%);
            z-index: 1;
        }

        /* Add focus styles for accessibility */
        .gauge-outer:focus-within {
            box-shadow: 0 0 0 3px rgba(0,122,255,0.15); /* Consistent focus shadow */
            outline: none;
        }

        /* Improve the Body Composition section container with Apple-like styling */
        #bodyMeasurementsSection {
            padding: 2rem 1.5rem; /* Adjusted padding */
            margin-top: 2rem;
            margin-bottom: 2rem;
            background: #f9f9f9; /* Simpler light background */
            border-radius: 16px; /* More rounded corners */
            box-shadow: none; /* Removed shadow for flatter design */
            border: 1px solid #e5e5e5; /* Add subtle border */
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            font-family: -apple-system, BlinkMacSystemFont, "SF Pro Text", "San Francisco", "Helvetica Neue", sans-serif; /* Apple system fonts */
        }

        /* Specific styling for Aging Rate card */
        #agingRateCard h3 {
            text-align: center; /* Center the title */
            justify-content: center; /* Center flex items (though ::before is removed) */
            display: block; /* Override flex display from .result-card h3 */
        }

        #agingRateCard h3::before {
            display: none; /* Remove the blue vertical bar */
        }

        #agingRateDisplay {
            text-align: center; /* Center the value and label */
            padding: 1rem 0; /* Add some padding around the value */
        }

        /* Adjust aging rate value specifically if needed */
        #agingRateDisplay .metric-value {
            font-size: 2.75rem; /* Make the aging rate value larger */
            margin-bottom: 0.25rem; /* Reduce space below value */
        }

        /* Adjust aging rate label specifically */
        #agingRateDisplay .metric-label {
             font-size: 0.9rem; /* Slightly smaller label */
             color: #86868b; /* Apple secondary text color */
             margin-top: 0; /* Remove top margin */
        }

        /* Arc Gauge Styling */
        .gauge-wrapper {
            width: 100%;
            margin: 1rem auto 0;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        #agingRateGaugeWrapper {
            margin-top: 1.5rem;
        }

        #agingRateGauge {
            max-width: 100%;
            height: auto;
        }

        #agingRateGauge text {
            font-family: -apple-system, BlinkMacSystemFont, "SF Pro Text", "San Francisco", "Helvetica Neue", sans-serif;
        }

        #agingRateNeedle {
            transition: transform 1s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        @media (max-width: 768px) {
            #agingRateGauge {
                width: 280px;
                height: 140px;
            }
        }


        .metric-value {
            font-size: 2.25rem; /* Slightly smaller */
            font-weight: 700;
        }

        /* Premium Styling for Recommendations Section */
        .ai-section > h5 + .ai-recommendations {
            margin-top: 1.5rem; /* Add space below the section title */
        }
        .ai-recommendations {
             /* Apply card styling to the container if needed, or keep it section-based */
        }
        .recommendations-list {
            margin-top: 0; /* Reset margin if container has padding */
            padding: 0.5rem 0; /* Add some vertical padding */
        }
        .recommendation-item {
            display: flex;
            margin-bottom: 1.5rem; /* Spacing between items */
            padding: 1.25rem; /* Increased padding within item */
            background-color: #ffffff; /* White background for card effect */
            border: 1px solid #e5e5e5; /* Softer border */
            border-radius: 12px; /* More rounded corners */
            transition: background-color 0.2s ease, transform 0.2s ease;
            box-shadow: 0 2px 4px rgba(0,0,0,0.03); /* Subtle shadow */
        }
        .recommendation-item:hover {
            background-color: #f8f9fa; /* Slight hover effect */
            transform: translateY(-1px);
        }
        .recommendation-number {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background-color: #007AFF;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 1rem;
            flex-shrink: 0;
            font-size: 0.9rem;
        }
        .recommendation-content {
            flex: 1;
            line-height: 1.5;
        }
        .recommendation-content strong {
            color: #1d1d1f;
            font-weight: 600; /* Slightly bolder title */
        }
        .recommendation-steps, .recommendation-benefit {
            margin-top: 0.85rem; /* Increased spacing */
            font-size: 0.9rem; /* Ensured >= 11pt */
            color: #545457;
        }
        .recommendation-steps strong, .recommendation-benefit strong {
            color: #333;
            font-weight: 600;
        }
        .recommendation-steps ol {
            padding-left: 1.5rem;
            margin-top: 0.4rem; /* Space above list items */
        }
         .recommendation-steps ol li {
            margin-bottom: 0.35rem; /* Space between list items */
            line-height: 1.45;
         }
        .explanation {
            font-size: 0.9rem; /* Ensured >= 11pt */
            margin-top: 0.4rem;
            color: #6c757d;
            line-height: 1.4;
        }
        /* Styling for Strengths/Priorities Lists */
        .ai-list {
            list-style: disc; /* Use default disc bullets */
            padding-left: 25px; /* Restore indentation for bullets */
            margin-left: 0; /* Reset margin if needed */
        }
        .ai-list-item {
            margin-bottom: 1rem;
            /* padding-left: 0; Removed - Let list-style handle padding */
            line-height: 1.5;
            list-style-position: outside; /* Ensure bullet is outside text flow */
        }
        /* Ensure icons are hidden */
        .list-icon { display: none !important; }

        /* Loading Animation Styles */
        .ai-status {
            /* Styles for the container holding loading/error */
            text-align: center;
            padding: 2rem 0;
        }
        .ai-loading {
             /* Styles are likely already applied via inline JS - display: flex */
             align-items: center;
             justify-content: center;
             gap: 1rem;
             color: #6c757d;
        }
        .ai-loading-icon {
             font-size: 1.8rem;
             color: #007AFF;
             /* Animation applied via JS/keyframes */
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .ai-error {
            color: #dc3545; /* Bootstrap danger color */
        }
        .ai-error h5 {
            margin-bottom: 0.5rem;
        }
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
        // These weights are like importance points for each health habit.
        // Think of them as "health points" - the bigger the number, the more that habit matters for your health age.
        // If you want to make a habit more important, just give it a bigger number!
        // Example: If exercise should be super important, you might change 0.2 to 0.4
        const weights = {
            // Body & Fitness Factors
            physicalActivity: 0.7,    // How much exercise matters for your health age - UPDATED
            sitToStand: 0.05,         // How much your ability to get up from a chair matters
            breathHold: 0.03,         // How much your breathing strength matters
            balance: 0.05,            // How much your balance matters
            
            // Sleep Factors
            sleepDuration: 0.7,      // How much sleep time matters - UPDATED
            sleepQuality: 0.15,       // How much good sleep matters
            
            // Mental & Social Factors
            stressLevels: 0.1,        // How much stress affects your health age
            socialConnections: 0.05,  // How much friends and family time matters
            cognitiveActivity: 0.05,  // How much brain exercise matters
            
            // Lifestyle Factors
            dietQuality: 0.2,         // How much healthy eating matters
            alcoholConsumption: 0.05, // How much drinking alcohol matters
            smokingStatus: 0.1,       // How much smoking matters
            supplementIntake: 0.02,   // How much vitamins and supplements matter
            sunlightExposure: 0.03,   // How much sunshine matters
            
            // Body Measurements
            bmiScore: 1.3,            // How much your weight-to-height ratio matters - UPDATED
            whrScore: 1.2,            // How much your waist-to-hip ratio matters - UPDATED
            skinElasticity: 0.02,     // How much your skin health matters
            overallHealthScore: 1.0   // ADDED - How much overall self-reported health matters
        };

        // --- End Calculation Weights ---

        // --- NEW: Function to calculate Overall Health Score based on percentage ---
        /**
         * Gives a health score based on the user's self-reported Overall Health Percentage.
         * Converts a percentage (0-100) into a score from 0-5.
         * 
         * These are the Overall Health Percentage grades:
         * >= 90%: 5 (Excellent)
         * >= 75%: 4 (Very Good)
         * >= 60%: 3 (Good/Average)
         * >= 45%: 2 (Fair)
         * >= 30%: 1 (Poor)
         * < 30%: 0 (Very Poor)
         * 
         * @param {number|null} overallHealthPercent - User's reported percentage, or null/undefined if not provided.
         * @returns {number} - The health score from 0-5 (defaults to 3 if no percentage provided).
         */
        function calculateOverallHealthScore(overallHealthPercent) {
            // If no percentage is provided or it's not a valid number, default to a neutral score of 3.
            if (overallHealthPercent === null || isNaN(overallHealthPercent)) {
                debug("Overall Health Percent not provided or invalid, defaulting score to 3.");
                return 3; 
            }
            
            debug(`Calculating Overall Health Score for percentage: ${overallHealthPercent}`);
            
            // Determine score based on percentage ranges
            if (overallHealthPercent >= 90) return 5;
            if (overallHealthPercent >= 75) return 4;
            if (overallHealthPercent >= 60) return 3;
            if (overallHealthPercent >= 45) return 2;
            if (overallHealthPercent >= 30) return 1;
            return 0; // If less than 30
        }
        // --- END NEW Function ---

        /**
         * Calculates Body Mass Index (BMI).
         * Think of this as your "body size number."
         * 
         * We take your weight and divide it by your height squared.
         * Like figuring out how much space you take up compared to how tall you are.
         * 
         * @param {number} heightCm - How tall you are in centimeters.
         * @param {number} weightKg - How much you weigh in kilograms.
         * @returns {number|NaN} - Your BMI number or NaN if we can't calculate it.
         */
        function calculateBMI(heightCm, weightKg) {
            // Check if height and weight are positive numbers
            if (!heightCm || heightCm <= 0 || !weightKg || weightKg <= 0) return NaN;
            // Turn height from centimeters to meters, then calculate
            return weightKg / ((heightCm / 100) ** 2);
        }

        /**
         * Calculates Waist-to-Hip Ratio (WHR).
         * This is your "body shape number."
         * 
         * We divide your waist size by your hip size.
         * It helps show if you carry weight more in your belly or your hips.
         * 
         * @param {number} waistCm - How big around your belly button is in centimeters.
         * @param {number} hipCm - How big around your hips are in centimeters.
         * @returns {number|NaN} - Your WHR number or NaN if we can't calculate it.
         */
        function calculateWHR(waistCm, hipCm) {
            // Check if measurements are positive numbers
            if (!waistCm || waistCm <= 0 || !hipCm || hipCm <= 0) return NaN;
            return waistCm / hipCm;
        }

        /**
         * Gives a health score based on your BMI.
         * Think of this as turning your BMI into a report card grade from 0-5.
         * 
         * 5 is the best score - like getting an A+
         * 1 is the worst score - like getting a D
         * 
         * These are the BMI grades:
         * < 18.5: 1 (Too thin)
         * 18.5 - 19.9: 3 (Good)
         * 20.0 - 22.0: 5 (Perfect!)
         * 22.1 - 25.0: 4 (Very good)
         * 25.1 - 27.5: 3 (Good)
         * 27.6 - 30.0: 2 (Not so good)
         * > 30.0: 1 (Too heavy)
         * 
         * @param {number} bmi - Your calculated BMI.
         * @returns {number} - Your BMI health score from 0-5.
         */
        function getBMIScore(bmi) {
            if (isNaN(bmi)) return 0; // If BMI is missing, score is 0
            if (bmi < 18.5) return 1;
            if (bmi < 20) return 3;
            if (bmi <= 22) return 5;
            if (bmi <= 25) return 4;
            if (bmi <= 27.5) return 3;
            if (bmi <= 30) return 2;
            return 1;
        }

        /**
         * Gives a health score based on your WHR and gender.
         * This turns your waist-hip ratio into a grade from 0-5.
         * 
         * Boys and girls have different healthy shapes, so we score them differently.
         * 5 is the best score (healthiest shape)
         * 1 is the lowest score (less healthy shape)
         * 
         * @param {number} whr - Your waist-to-hip ratio.
         * @param {string} gender - If you're a boy or girl ("male", "female", or "other").
         * @returns {number} - Your shape health score from 0-5.
         */
        function getWHRScore(whr, gender) {
            if (isNaN(whr) || !gender) return 0; // If WHR is missing, score is 0
            const lowerCaseGender = gender.toLowerCase();

            // Girls' scoring:
            if (lowerCaseGender === "female") {
                if (whr <= 0.75) return 5; // Super healthy shape
                if (whr <= 0.80) return 4; // Very healthy shape
                if (whr <= 0.85) return 3; // Healthy shape
                if (whr <= 0.90) return 2; // Less healthy shape
                return 1; // Least healthy shape
            } else { // Boys' scoring:
                if (whr <= 0.85) return 5; // Super healthy shape
                if (whr <= 0.90) return 4; // Very healthy shape
                if (whr <= 0.95) return 3; // Healthy shape
                if (whr <= 1.00) return 2; // Less healthy shape
                return 1; // Least healthy shape
            }
        }

        /**
         * Calculates how much older or younger your body is compared to your actual age.
         * Think of this as your "health bonus or penalty years."
         * 
         * If you have good health habits, you get minus years (younger body age).
         * If you have bad health habits, you get plus years (older body age).
         * 
         * We look at all your health habits, and give each one a score.
         * Then we add them all up to see how many years to add or subtract.
         * 
         * @param {object} scores - All your health scores for different habits.
         * @param {number} age - Your real age in years.
         * @returns {number} - How many years to add or subtract from your age.
         */
        function calculateAgeShift(scores, age) {
            let totalShift = 0;
            debug("Calculating age shift. Initial age:", age);
            debug("Scores being used for shift:", scores);
            debug("Weights used:", weights);

            // Look at each health habit and calculate its effect
            for (let metric in weights) {
                const score = scores[metric]; // Get your score for this habit
                
                if (typeof score === 'number' && !isNaN(score)) {
                    // How this works:
                    // 3 is an average score
                    // If your score is higher than 3, you get minus years (younger)
                    // If your score is lower than 3, you get plus years (older)
                    // We multiply by the importance of each habit (its weight)
                    const shiftContribution = weights[metric] * (3 - score);
                    totalShift += shiftContribution;
                    debug(`Metric: ${metric}, Score: ${score}, Weight: ${weights[metric]}, Shift Contribution: ${shiftContribution.toFixed(2)}`);
                } else {
                    debug(`Invalid or missing score for metric: ${metric}. Skipping.`);
                }
            }
            debug("Total shift before age adjustment:", totalShift.toFixed(2));

            // Make the results more realistic based on your age
            // Age adjustments explained in simple terms:
            
            if (age < 25 && totalShift < 0) {
                // Young people (under 25) with good habits:
                // We reduce the "younger" effect, because young people are already young!
                // You can only be so much "younger" than your actual age when you're already young.
                const adjustment = Math.max(totalShift * 0.3, -(age * 0.2));
                debug(`Age < 25 adjustment: ${adjustment.toFixed(2)} (from ${totalShift.toFixed(2)})`);
                totalShift = adjustment;
            } else if (age < 35) {
                // People under 35:
                // We reduce the effect by half, since age differences aren't as dramatic
                // for younger adults
                const adjustment = totalShift * 0.5;
                debug(`Age < 35 adjustment: ${adjustment.toFixed(2)} (from ${totalShift.toFixed(2)})`);
                totalShift = adjustment;
            } else if (age > 65) {
                // Older people (over 65):
                // We reduce the effect, since at older ages some aging is normal
                // and can't be completely avoided
                const adjustment = totalShift * 0.7;
                debug(`Age > 65 adjustment: ${adjustment.toFixed(2)} (from ${totalShift.toFixed(2)})`);
                totalShift = adjustment;
            }
            debug("Final age shift:", totalShift.toFixed(2));
            return totalShift;
        }

        /**
         * Calculates your "body age" based on health habits.
         * Think of this as your "real body age" versus your birthday age.
         * 
         * Your birthday age is how many years since you were born.
         * Your body age is how old your body seems based on your health.
         * 
         * If you have healthy habits, your body might be "younger" than your birthday age.
         * If you have unhealthy habits, your body might be "older" than your birthday age.
         * 
         * Example: A 40-year-old with great habits might have a body age of 35!
         * 
         * @param {number} chronologicalAge - Your birthday age (years since birth).
         * @param {number} ageShift - Years to add/subtract based on health habits.
         * @returns {number|NaN} - Your estimated body age.
         */
        function calculateBiologicalAge(chronologicalAge, ageShift) {
            // Scale factor - we don't apply 100% of the age shift
            // This makes the results more realistic (not too extreme)
            const scalingFactor = 0.8; // We use 80% of the calculated age shift
            
            if (isNaN(chronologicalAge) || isNaN(ageShift)) return NaN; // Check for valid numbers
            
            const bioAge = chronologicalAge + (ageShift * scalingFactor);
            debug(`Calculated Biological Age: ${bioAge.toFixed(1)} (Chrono: ${chronologicalAge}, Shift: ${ageShift.toFixed(2)}, Scale: ${scalingFactor})`);
            return bioAge;
        }

        /**
         * Calculates your "aging speed."
         * This is like your "health speedometer" - are you aging faster or slower than normal?
         * 
         * Think of it like this:
         * - If the number equals 1.0: You're aging at a normal speed
         * - If the number is over 1.0: You're aging faster than normal (not good)
         * - If the number is under 1.0: You're aging slower than normal (good!)
         * 
         * Example: 
         * - 0.9 means you're aging 10% slower than average (great!)
         * - 1.2 means you're aging 20% faster than average (not so good)
         * 
         * @param {number} biologicalAge - Your calculated body age.
         * @param {number} chronologicalAge - Your actual birthday age.
         * @returns {number|NaN} - Your aging speed (rate).
         */
        function calculateAgingRate(biologicalAge, chronologicalAge) {
            // Make sure we have valid numbers and don't divide by zero
            if (isNaN(biologicalAge) || !chronologicalAge || chronologicalAge <= 0) return NaN;
            
            const rate = biologicalAge / chronologicalAge;
            debug(`Calculated Aging Rate: ${rate.toFixed(2)} (BioAge: ${biologicalAge.toFixed(1)}, ChronoAge: ${chronologicalAge})`);
            return rate;
        }

        /**
         * Initiates an AI analysis of the user's longevity assessment data.
         */
        function performAIAnalysis(scores, measurements, age, biologicalAge, ageShift, agingRate, bmi, bmiCategory, whr, whrCategory, positiveFactors, negativeFactors) {
            debug("Starting AI analysis...");
            
            // Show loading indicator
            const aiSection = document.getElementById('aiAnalysisSection');
            const loadingDiv = aiSection.querySelector('.ai-loading');
            const contentDiv = aiSection.querySelector('.ai-content');
            const statusDiv = aiSection.querySelector('.ai-status'); // Get status container
            
            if (!loadingDiv || !contentDiv || !statusDiv) { // Check for statusDiv too
                console.error("AI Analysis section elements not found!");
                return;
            }
            
            // Show loading animation and hide content
            statusDiv.style.display = 'block'; // Make sure status container is visible
            loadingDiv.style.display = 'flex';
            loadingDiv.querySelector('.ai-loading-icon').style.animation = 'spin 1.5s linear infinite';
            contentDiv.style.display = 'none'; // Hide previous results/errors
            
            // Add a spinning animation (ensure keyframes are in CSS now)
            // const style = document.createElement('style');
            // style.textContent = ` ... keyframes ... `;
            // document.head.appendChild(style);
            
            // Prepare data for API
            const analysisData = {
                age: age,
                gender: measurements.gender,
                biologicalAge: biologicalAge.toFixed(1),
                ageShift: ageShift.toFixed(1),
                agingRate: agingRate.toFixed(2),
                bmi: bmi.toFixed(1),
                bmiCategory: bmiCategory,
                whr: whr.toFixed(2),
                whrCategory: whrCategory,
                scores: scores,
                positiveFactors: positiveFactors,
                negativeFactors: negativeFactors
            };
            
            debug("Sending AI analysis data:", analysisData);
            
            // Check if longevity_form_data exists
            if (!window.longevity_form_data) {
                console.error("Cannot perform AI analysis: longevity_form_data is not defined");
                displayAIError('Configuration error. Please contact the site administrator.');
                return;
            }
            
            // Make AJAX request to the server
            $.ajax({
                url: window.longevity_form_data.ajax_url,
                type: 'POST',
                data: {
                    action: 'longevity_ai_analysis',
                    security: window.longevity_form_data.nonce,
                    analysis_data: JSON.stringify(analysisData)
                },
                success: function(response) {
                    debug("AI analysis response received:", response);
                    
                    // Hide loading animation
                    statusDiv.style.display = 'none'; // Hide the whole status container on success
                    loadingDiv.style.display = 'none';
                    
                    if (response.success && response.data) {
                        // Display the AI analysis results
                        displayAIAnalysis(response.data);
                        // Show the content div
                        contentDiv.style.display = 'block';
                    } else {
                        // Display error message with more detail
                        let errorMsg = 'Error analyzing your data.';
                        if (response.data && response.data.message) {
                            errorMsg = response.data.message;
                        }
                        debug("AI analysis error:", errorMsg);
                        displayAIError(errorMsg);
                    }
                },
                error: function(xhr, status, error) {
                    debug("AI analysis request failed:", {xhr: xhr, status: status, error: error});
                    
                    // Hide loading animation, keep status div visible to show error
                    loadingDiv.style.display = 'none'; 
                    statusDiv.style.display = 'block'; 
                    
                    // Attempt to parse response for more details
                    let errorMsg = 'Error connecting to the analysis service. Please try again later.';
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response.data && response.data.message) {
                            errorMsg = response.data.message;
                        }
                    } catch (e) {
                        // If we can't parse the response, use the default error message
                    }
                    
                    // Display error message
                    displayAIError(errorMsg);
                }
            });
        }
        
        /**
         * Displays the AI analysis results in the UI.
         * @param {object} data - The AI analysis data containing summary, strengths, priorities, recommendations.
         */
        function displayAIAnalysis(data) {
            debug("Displaying AI analysis:", data);
            
            const aiSection = document.getElementById('aiAnalysisSection');
            if (!aiSection) {
                console.error("AI Analysis section not found!");
                return;
            }
            
            const contentDiv = aiSection.querySelector('.ai-content');
            if (!contentDiv) {
                console.error("AI Analysis content div not found!");
                return;
            }
            
            // Clear previous content before adding new structure
            contentDiv.innerHTML = `
                <div class="ai-header">
                    <span class="material-icons ai-icon">psychology</span>
                    <div class="ai-branding">
                        <h4>AI Health Insights</h4>
                        <p class="ai-subtitle">Personalized analysis based on your assessment</p>
                    </div>
                </div>
                <div class="ai-section">
                    <h5>Summary</h5>
                    <div class="ai-summary"></div>
                </div>
                <div class="ai-columns">
                    <div class="ai-column">
                        <h5>Key Strengths</h5>
                        <div class="ai-strengths"></div>
                    </div>
                    <div class="ai-column">
                        <h5>Priority Areas</h5>
                        <div class="ai-priorities"></div>
                    </div>
                </div>
                <div class="ai-section">
                    <h5>Personalized Recommendations</h5>
                    <div class="ai-recommendations"></div>
                </div>
            `;
            
            const summaryDiv = contentDiv.querySelector('.ai-summary');
            const strengthsDiv = contentDiv.querySelector('.ai-strengths');
            const prioritiesDiv = contentDiv.querySelector('.ai-priorities');
            const recommendationsDiv = contentDiv.querySelector('.ai-recommendations');
            
            // Display summary
            summaryDiv.innerHTML = `<p>${data.summary || 'No summary available.'}</p>`;
            
            // Helper function to get text from potential object/string
            const getText = (item, defaultProp = 'text') => {
                if (typeof item === 'string') {
                    return item;
                }
                if (typeof item === 'object' && item !== null) {
                    // Prioritize specific keys, then defaultProp, then fallback
                    return item.point || item.title || item.name || item.strength || item.area || item.recommendation || item[defaultProp] || 'Processing error: Invalid data format';
                }
                return 'Processing error: Invalid data format';
            };

            const getExplanation = (item) => {
                if (typeof item === 'object' && item !== null) {
                    return item.explanation || item.why || '';
                }
                return '';
            };

            // Display strengths
            let strengthsHtml = '';
            if (data.strengths && data.strengths.length > 0) {
                strengthsHtml = '<ul class="ai-list">';
                data.strengths.forEach(strength => {
                    const strengthText = getText(strength, 'strength');
                    const explanation = getExplanation(strength);
                    
                    // Removed the manual bullet point (•)
                    strengthsHtml += `<li class="ai-list-item">
                        <div>
                            <strong>${strengthText}</strong>
                            ${explanation ? `<p class="explanation">${explanation}</p>` : ''}
                        </div>
                    </li>`;
                });
                strengthsHtml += '</ul>';
            } else {
                strengthsHtml = '<p>No key strengths identified.</p>';
            }
            strengthsDiv.innerHTML = strengthsHtml;
            
            // Display priorities
            let prioritiesHtml = '';
            if (data.priorities && data.priorities.length > 0) {
                prioritiesHtml = '<ul class="ai-list">';
                data.priorities.forEach(priority => {
                    const priorityText = getText(priority, 'priority');
                    const explanation = getExplanation(priority);
                    
                    // Removed the manual bullet point (•)
                    prioritiesHtml += `<li class="ai-list-item">
                        <div>
                            <strong>${priorityText}</strong>
                            ${explanation ? `<p class="explanation">${explanation}</p>` : ''}
                        </div>
                    </li>`;
                });
                prioritiesHtml += '</ul>';
            } else {
                prioritiesHtml = '<p>No priority areas identified.</p>';
            }
            prioritiesDiv.innerHTML = prioritiesHtml;
            
            // Display recommendations
            let recommendationsHtml = '';
            if (data.recommendations && data.recommendations.length > 0) {
                recommendationsHtml = '<ul class="ai-list recommendations-list">';
                data.recommendations.forEach((recommendation, index) => {
                    // --- REMOVED TEMPORARY DEBUGGING ---
                    
                    let recommendationContent = '';
                    if (typeof recommendation === 'string') {
                        recommendationContent = `<strong>${recommendation}</strong>`; 
                    } else if (typeof recommendation === 'object' && recommendation !== null) {
                        // Directly access the properties based on the debugged structure
                        let what = recommendation.change || 'Recommendation details missing'; // Use 'change'
                        let how = recommendation.implementation || ''; // Use 'implementation'
                        let benefit = recommendation.benefit || ''; // Use 'benefit'
                        
                        recommendationContent = `<strong>${what}</strong>`;
                        
                        if (how) {
                            if (Array.isArray(how)) {
                                recommendationContent += '<div class="recommendation-steps"><strong>How:</strong><ol>';
                                how.forEach(step => {
                                    recommendationContent += `<li>${step}</li>`;
                                });
                                recommendationContent += '</ol></div>';
                            } else {
                                recommendationContent += `<div class="recommendation-steps"><strong>How:</strong> ${how}</div>`;
                            }
                        }
                        
                        if (benefit) {
                            recommendationContent += `<div class="recommendation-benefit"><strong>Benefit:</strong> ${benefit}</div>`;
                        }
                    } else {
                        recommendationContent = 'Invalid recommendation format';
                    }
                    
                    recommendationsHtml += `
                        <li class="recommendation-item">
                            <div class="recommendation-number">${index + 1}</div>
                            <div class="recommendation-content">${recommendationContent}</div>
                        </li>`;
                });
                recommendationsHtml += '</ul>';
            } else {
                recommendationsHtml = '<p>No recommendations available.</p>';
            }
            recommendationsDiv.innerHTML = recommendationsHtml;
            
            // Update or add CSS - ensure previous styles aren't duplicated
            let styleElement = document.getElementById('ai-analysis-styles');
            if (!styleElement) {
                styleElement = document.createElement('style');
                styleElement.id = 'ai-analysis-styles'; 
                document.head.appendChild(styleElement);
            }
            styleElement.textContent = `
                .ai-list {
                    list-style: disc; /* Use default disc bullets */
                    padding-left: 25px; /* Restore indentation for bullets */
                    margin-left: 0; /* Reset margin if needed */
                }
                /* Premium Styling for Recommendations Section */
                .ai-section > h5 + .ai-recommendations {
                    margin-top: 1.5rem; /* Add space below the section title */
                }
                .ai-recommendations {
                     /* Apply card styling to the container if needed, or keep it section-based */
                }
                .recommendations-list {
                    margin-top: 0; /* Reset margin if container has padding */
                    padding: 0.5rem 0; /* Add some vertical padding */
                }
                .recommendation-item {
                    display: flex;
                    margin-bottom: 1.5rem; /* Spacing between items */
                    padding: 1.25rem; /* Increased padding within item */
                    background-color: #ffffff; /* White background for card effect */
                    border: 1px solid #e5e5e5; /* Softer border */
                    border-radius: 12px; /* More rounded corners */
                    transition: background-color 0.2s ease, transform 0.2s ease;
                    box-shadow: 0 2px 4px rgba(0,0,0,0.03); /* Subtle shadow */
                }
                .recommendation-item:hover {
                    background-color: #f8f9fa; /* Slight hover effect */
                    transform: translateY(-1px);
                }
                .recommendation-number {
                    width: 30px;
                    height: 30px;
                    border-radius: 50%;
                    background-color: #007AFF;
                    color: white;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-weight: bold;
                    margin-right: 1rem;
                    flex-shrink: 0;
                    font-size: 0.9rem;
                }
                .recommendation-content {
                    flex: 1;
                    line-height: 1.5;
                }
                .recommendation-content strong {
                    color: #1d1d1f;
                    font-weight: 600; /* Slightly bolder title */
                }
                .recommendation-steps, .recommendation-benefit {
                    margin-top: 0.85rem; /* Increased spacing */
                    font-size: 0.9rem; /* Ensured >= 11pt */
                    color: #545457;
                }
                .recommendation-steps strong, .recommendation-benefit strong {
                    color: #333;
                    font-weight: 600;
                }
                .recommendation-steps ol {
                    padding-left: 1.5rem;
                    margin-top: 0.4rem; /* Space above list items */
                }
                 .recommendation-steps ol li {
                    margin-bottom: 0.35rem; /* Space between list items */
                    line-height: 1.45;
                 }
                .explanation {
                    font-size: 0.9rem; /* Ensured >= 11pt */
                    margin-top: 0.4rem;
                    color: #6c757d;
                    line-height: 1.4;
                }
                /* Styling for Strengths/Priorities Lists */
                .ai-list-item {
                    margin-bottom: 1rem; 
                    padding-left: 0;
                    line-height: 1.5;
                    list-style-position: outside; /* Ensure bullet is outside text flow */
                }
                .ai-list-item strong {
                     color: #1d1d1f;
                     font-weight: 600;
                }
                /* Ensure icons are hidden */
                .list-icon { display: none !important; }

                /* Loading Animation Styles */
                .ai-status {
                    /* Styles for the container holding loading/error */
                    text-align: center;
                    padding: 2rem 0;
                }
                .ai-loading {
                     /* Styles are likely already applied via inline JS - display: flex */
                     align-items: center;
                     justify-content: center;
                     gap: 1rem;
                     color: #6c757d;
                }
                .ai-loading-icon {
                     font-size: 1.8rem;
                     color: #007AFF;
                     /* Animation applied via JS/keyframes */
                }
                @keyframes spin {
                    0% { transform: rotate(0deg); }
                    100% { transform: rotate(360deg); }
                }
                .ai-error {
                    color: #dc3545; /* Bootstrap danger color */
                }
                .ai-error h5 {
                    margin-bottom: 0.5rem;
                }
            `;
            
            // Show the content
            contentDiv.style.display = 'block';
        }
        
        /**
         * Displays an error message in the AI Analysis section.
         * @param {string} message - The error message to display.
         */
        function displayAIError(message) {
            debug("Displaying AI error:", message);
            
            const aiSection = document.getElementById('aiAnalysisSection');
            if (!aiSection) {
                console.error("AI Analysis section not found!");
                return;
            }
            
            const contentDiv = aiSection.querySelector('.ai-content');
            if (!contentDiv) {
                console.error("AI Analysis content div not found!");
                return;
            }
            
            // Display error message
            contentDiv.innerHTML = `
                <div class="ai-header">
                    <span class="material-icons ai-icon">psychology</span>
                    <div class="ai-branding">
                        <h4>AI Health Insights</h4>
                        <p class="ai-subtitle">Personalized analysis based on your assessment</p>
                    </div>
                </div>
                <div class="ai-error">
                    <span class="material-icons" style="font-size: 2rem; color: #e74c3c; margin-bottom: 1rem;">error_outline</span>
                    <h5>Analysis Error</h5>
                    <p>${message}</p>
                    <p>Your assessment results are still valid and available below.</p>
                </div>
            `;
            
            // Show the content
            contentDiv.style.display = 'block';
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

            // Calculate Age-related metrics
            const ageShift = calculateAgeShift(scores, age);
            const biologicalAge = calculateBiologicalAge(age, ageShift);
            const agingRate = calculateAgingRate(biologicalAge, age);

            // --- Populate Biological Age Card ---
            const biologicalAgeDiv = document.getElementById('biologicalAgeDisplay');
            if (biologicalAgeDiv) {
                // Display Biological Age and the Age Shift in parentheses
                //const ageShiftText = !isNaN(ageShift) ? ` (${ageShift > 0 ? '+' : ''}${ageShift.toFixed(1)} years)` : '';
                let ageShiftHtml = '';
                if (!isNaN(ageShift)) {
                    const shiftValue = ageShift.toFixed(1);
                    const plusSign = ageShift > 0 ? '+' : '';
                    let shiftClass = 'age-shift-value';
                    if (ageShift > 0.05) { // Add a small threshold to avoid coloring for very tiny shifts
                        shiftClass += ' age-shift-positive';
                    } else if (ageShift < -0.05) {
                        shiftClass += ' age-shift-negative';
                    }
                    ageShiftHtml = ` (<span class="${shiftClass}">${plusSign}${shiftValue} years</span>)`;
                }
                
                biologicalAgeDiv.innerHTML = `
                    <div class="metric-value">${!isNaN(biologicalAge) ? biologicalAge.toFixed(1) : 'N/A'} years</div>
                    <div class="metric-label">vs Chronological Age: ${age}${ageShiftHtml}</div>
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
             const agingRateDiv = document.getElementById('agingRateDisplay'); // Keep reference for potential future use or remove if completely unused
             const agingRateContainer = document.getElementById('zingChartAgingRateGaugeContainer'); // Get ZingChart container

             if(agingRateContainer) { // Check if ZingChart container exists
                 let rateText = 'N/A';
                 let interpretation = 'Not Calculated';
                 if (!isNaN(agingRate)) {
                     rateText = agingRate.toFixed(2);
                     if (agingRate > 1.05) interpretation = 'Faster'; // Simplified interpretation for rules
                     else if (agingRate < 0.95) interpretation = 'Slower';
                     else interpretation = 'Average';
                 }

                 // *** NEW: ZingChart Configuration for Aging Rate ***
                 ZC.LICENSE = ["569d52cefae586f634c54f86dc99e6a9", "b55b025e438fa8a98e32482b5f768ff5"]; // Replace with your license key

                 const agingRateChartConfig = {
                   type: "gauge",
                   globals: {
                     fontSize: 16 // Adjusted base font size
                   },
                   plotarea: {
                     marginTop: 40,
                     marginBottom: 40 // Add bottom margin
                   },
                   plot: {
                     size: '100%',
                     valueBox: {
                       placement: 'center',
                       text: '%v', // Display the aging rate value
                       fontSize: 30,
                       paddingBottom: 30, // Push value up slightly
                       rules: [ // Rules to add text below the value
                         { rule: '%v < 0.95', text: '%v<br><span style="font-size:18px;color:#00A99D;">Slower</span>' },
                         { rule: '%v >= 0.95 && %v <= 1.05', text: '%v<br><span style="font-size:18px;color:#888888;">Average</span>' },
                         { rule: '%v > 1.05', text: '%v<br><span style="font-size:18px;color:#F58220;">Faster</span>' }
                       ]
                     }
                   },
                   tooltip: {
                     borderRadius: 5,
                     text: "Aging Rate: %v"
                   },
                   scaleR: {
                     aperture: 180, // Half circle
                     minValue: 0.6,
                     maxValue: 1.4,
                     step: 0.1,
                     center: { visible: false },
                     tick: { visible: false },
                     item: { // Label styling
                       offsetR: 0,
                       fontSize: 12,
                       fontColor: '#555'
                     },
                     // Labels for the scale markers
                     labels: ['0.6', '0.7', '0.8', '0.9', '1.0', '1.1', '1.2', '1.3', '1.4'],
                     ring: { // Colored background segments
                       size: 35, // Thickness of the ring
                       rules: [
                         { rule: '%v < 0.95', backgroundColor: '#00A99D' }, // Teal for Slower
                         { rule: '%v >= 0.95 && %v <= 1.05', backgroundColor: '#CCCCCC' }, // Grey for Average
                         { rule: '%v > 1.05', backgroundColor: '#F58220' } // Orange for Faster
                       ]
                     }
                   },
                   series: [{
                     values: [isNaN(agingRate) ? 1.0 : parseFloat(rateText)], // Use calculated rate, default to 1.0 if NaN
                     backgroundColor: '#4A4A4A', // Needle color
                     indicator: [10, 1, 10, 10, 0.6], // Needle shape/size [width1, width2, length1, length2, alpha]
                     animation: {
                       effect: 2, method: 1, sequence: 4, speed: 900
                     },
                   }]
                 };

                 // Render the ZingChart
                 zingchart.render({
                   id: 'zingChartAgingRateGaugeContainer',
                   data: agingRateChartConfig,
                   height: '100%', // Use container height
                   width: '100%'
                 });
                 // *** END: ZingChart Configuration ***

                 debug("Aging Rate ZingChart rendered.");
             } else {
                 console.error("Element with ID 'zingChartAgingRateGaugeContainer' not found!");
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
                                    BMI (Body Mass Index) measures weight relative to height. A BMI between 18.5-24.9 is considered healthy (green zone), 25-29.9 is overweight (yellow zone), and 30+ is obese (red zone).
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
                                    WHR (Waist-to-Hip Ratio) measures body fat distribution. ${isFemale ? 'For women, a WHR of 0.8 or less indicates low health risk (green zone).' : 'For men, a WHR of 0.95 or less indicates low health risk (green zone).'}
                                </div>
                            </div>
                        </div>
                    `;
                } else {
                    whrHtml = `<div class="gauge-metric"><div class="gauge-label">WHR: N/A</div></div>`;
                }

                bodyMeasurementsDiv.innerHTML = bmiHtml + whrHtml;
                debug("Body Measurements HTML updated with gauges.");
                
                // Add subtle animation to the markers after content is rendered
                setTimeout(() => {
                    const markers = bodyMeasurementsDiv.querySelectorAll('.gauge-marker');
                    markers.forEach(marker => {
                        marker.style.transition = 'transform 0.4s cubic-bezier(0.34, 1.56, 0.64, 1)';
                        marker.style.transform = 'translateX(-50%) translateY(-2px)';
                        setTimeout(() => {
                            marker.style.transform = 'translateX(-50%) translateY(0)';
                        }, 100);
                    });
                }, 300);

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
                        // Only calculate impact for factors that have a score provided by the user
                        if (typeof score === 'number' && !isNaN(score)) {
                            // Calculate impact (negative values mean they're adding age, positive values mean reducing age)
                            // This is because higher scores are better, and we want to show how much they add/subtract from age
                            impactValues[factor] = weights[factor] * (score - 3);
                        }
                    }
                }
                
                // If no impact values calculated, show a message
                if (Object.keys(impactValues).length === 0) {
                    return {
                        positive: `
                            <div class="impact-factor">
                                <div class="factor-content">
                                    <div class="factor-description" style="text-align: center; padding: 20px 0;">
                                        No data available. Please answer some questions to see impact factors.
                                    </div>
                                </div>
                            </div>
                        `,
                        negative: `
                            <div class="impact-factor">
                                <div class="factor-content">
                                    <div class="factor-description" style="text-align: center; padding: 20px 0;">
                                        No data available. Please answer some questions to see impact factors.
                                    </div>
                                </div>
                            </div>
                        `
                    };
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

            // --- Populate Detailed Breakdown Section with Toggle and Charts ---
            const breakdownSection = document.getElementById('detailedBreakdownSection');
            const detailedBreakdownDiv = document.getElementById('detailedBreakdown');
            // Filter out overallHealthScore as it has no user input
            const breakdownKeys = Object.keys(weights).filter(key => key !== 'overallHealthScore');

            if (breakdownSection && detailedBreakdownDiv) {
                // Clear previous content and add structure
                detailedBreakdownDiv.innerHTML = '';

                // Add Chart Container - only the Polar Chart container
                const scoreChartContainer = document.createElement('div');
                scoreChartContainer.id = 'scoreChartContainer';
                scoreChartContainer.className = 'chart-container visible';
                detailedBreakdownDiv.appendChild(scoreChartContainer);

                // Create Chart - only the Polar Chart
                try {
                    createScoreRadarChart(scores, breakdownKeys, scoreChartContainer);
                    debug("Detailed Breakdown Chart created.");
                } catch (error) {
                    console.error("Error creating chart:", error);
                    detailedBreakdownDiv.innerHTML += '<p style="text-align:center; color: red;">Error displaying chart.</p>';
                }
            } else {
                console.error("Detailed Breakdown section or div not found!");
            }

            // --- Scroll to Results ---
            resultsSection.scrollIntoView({ behavior: 'smooth' });
            debug("Scrolled to results section.");
             
            // --- Collect top factors for AI analysis ---
            function getTopFactors() {
                const factorDetails = {
                    physicalActivity: { name: "Physical Activity" },
                    sleepDuration: { name: "Sleep Duration" },
                    sleepQuality: { name: "Sleep Quality" },
                    stressLevels: { name: "Stress Management" },
                    socialConnections: { name: "Social Connections" },
                    dietQuality: { name: "Diet Quality" },
                    alcoholConsumption: { name: "Alcohol Consumption" },
                    smokingStatus: { name: "Smoking Status" },
                    cognitiveActivity: { name: "Cognitive Activity" },
                    sunlightExposure: { name: "Sunlight Exposure" },
                    supplementIntake: { name: "Supplement Use" },
                    bmiScore: { name: "Body Mass Index" },
                    whrScore: { name: "Waist-to-Hip Ratio" },
                    sitToStand: { name: "Functional Strength" },
                    breathHold: { name: "Respiratory Function" },
                    balance: { name: "Balance Ability" },
                    skinElasticity: { name: "Skin Health" }
                };
                
                // Calculate impact values
                const impactValues = {};
                for (let factor in scores) {
                    if (factor in weights && factor in factorDetails) {
                        const score = scores[factor];
                        impactValues[factor] = weights[factor] * (score - 3);
                    }
                }
                
                // Sort and separate factors
                const sortedFactors = Object.keys(impactValues)
                    .filter(factor => factorDetails[factor])
                    .sort((a, b) => Math.abs(impactValues[b]) - Math.abs(impactValues[a]));
                
                const positiveFactors = sortedFactors
                    .filter(factor => impactValues[factor] > 0)
                    .map(factor => ({
                        name: factorDetails[factor].name,
                        impact: impactValues[factor]
                    }));
                
                const negativeFactors = sortedFactors
                    .filter(factor => impactValues[factor] < 0)
                    .map(factor => ({
                        name: factorDetails[factor].name,
                        impact: impactValues[factor]
                    }));
                    
                return { positiveFactors, negativeFactors };
            }
            
            // Get the top factors
            const { positiveFactors, negativeFactors } = getTopFactors();
            
            // --- Perform AI Analysis ---
            const bmiCategory = getBMICategory(bmi);
            const whrCategory = getWHRCategory(whr, measurements.gender);
            
            // Check if we have the necessary data for the API call
            if (window.longevity_form_data && window.Chart) { // Also check for Chart.js
                // Perform AI analysis with all the calculated data
                performAIAnalysis(
                    scores, 
                    measurements, 
                    age, 
                    biologicalAge, 
                    ageShift, 
                    agingRate, 
                    bmi, 
                    bmiCategory, 
                    whr, 
                    whrCategory, 
                    positiveFactors, 
                    negativeFactors
                );
            } else {
                console.error("Cannot perform AI analysis: longevity_form_data or Chart.js missing");
                displayAIError('Configuration error prevented AI analysis.'); // Use the existing error display
            }
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
                    overallHealthPercent: parseFloat(formData.get('overallHealthPercent')) || null, // Get 'overallHealthPercent' field, null if empty/invalid
                    gender: formData.get('gender'),                 // Get 'gender' field (string)
                    age: parseInt(formData.get('age'), 10) || 0      // Get 'age' field, convert to integer (base 10), default 0
                };

                // Extract score values from select dropdowns, converting to numbers
                // MODIFIED: Don't provide a default, only add answered questions to scores object
                const scoreKeys = [
                    'activity', 'sleepDuration', 'sleepQuality', 'stressLevels',
                    'socialConnections', 'dietQuality', 'alcoholConsumption', 'smokingStatus',
                    'cognitiveActivity', 'sunlightExposure', 'supplementIntake', 'sitStand',
                    'breathHold', 'balance', 'skinElasticity'
                ];
                const scores = {};
                scoreKeys.forEach(key => {
                    // Assumes the input field's `name` attribute matches the key.
                    const inputName = key; 
                    // Map form field names to weights property names defined in the 'weights' object
                    // This ensures we use the correct key when accessing weights later.
                    const weightKeyMap = {
                        'activity': 'physicalActivity',
                        'sitStand': 'sitToStand',
                        'sleepDuration': 'sleepDuration', // Keep mapping even if key is same
                        'sleepQuality': 'sleepQuality',
                        'stressLevels': 'stressLevels',
                        'socialConnections': 'socialConnections',
                        'dietQuality': 'dietQuality',
                        'alcoholConsumption': 'alcoholConsumption',
                        'smokingStatus': 'smokingStatus',
                        'cognitiveActivity': 'cognitiveActivity',
                        'sunlightExposure': 'sunlightExposure',
                        'supplementIntake': 'supplementIntake',
                        'breathHold': 'breathHold',
                        'balance': 'balance',
                        'skinElasticity': 'skinElasticity'
                        // bmiScore and whrScore are added later after calculation
                        // overallHealthScore is added later after calculation
                    };
                    const scoreKey = weightKeyMap[inputName] || inputName; // Use mapped key if exists
                    
                    const rawValue = formData.get(inputName);
                    
                    // Only include values that were actually selected by the user
                    if (rawValue && rawValue.trim() !== '') {
                        const value = parseInt(rawValue, 10); // Get value, convert to integer
                        
                        // Only add if it's a valid number
                        if (!isNaN(value)) {
                            scores[scoreKey] = value; // Use the mapped key
                        }
                    }
                });

                // --- NEW: Calculate Overall Health Score and add it to scores ---
                scores.overallHealthScore = calculateOverallHealthScore(measurements.overallHealthPercent);
                debug("Calculated Overall Health Score:", scores.overallHealthScore);
                // --- END NEW ---


                debug("Collected Measurements:", measurements);
                debug("Collected Scores (Lifestyle & Overall):", scores);

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
                
                // Map form field names to weights property names to ensure connection
                const weightPropertyMap = {
                    'activity': 'physicalActivity',
                    'sitStand': 'sitToStand'
                };
                
                // Add mapped properties to scores object
                Object.keys(weightPropertyMap).forEach(formField => {
                    if (scores[formField] !== undefined) {
                        scores[weightPropertyMap[formField]] = scores[formField];
                        debug(`Mapped ${formField} (${scores[formField]}) to ${weightPropertyMap[formField]}`);
                    }
                });
                
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
             
             // Register Chart.js plugins if they exist
             if (window.Chart && window.ChartAnnotation) {
                 Chart.register(ChartAnnotation);
                 debug("Chart.js Annotation plugin registered");
             }
             
            setupFormListener(); // Call the function to attach the listener to the form
        });

        // --- Chart Creation Functions ---

        /**
         * Creates the Score Radar Chart.
         * @param {object} scores - Object containing user scores.
         * @param {array} breakdownKeys - Array of metric keys.
         * @param {HTMLElement} containerElement - The DOM element to render the chart in.
         */
        function createScoreRadarChart(scores, breakdownKeys, containerElement) {
            debug("Creating Score Radar Chart...");
            if (!window.Chart) {
                console.error("Chart.js not loaded!");
                containerElement.innerHTML = '<p style="text-align:center; color: red;">Chart library not loaded.</p>';
                return;
            }

            // Filter breakdownKeys to only include metrics that have values in the scores object
            const filteredBreakdownKeys = breakdownKeys.filter(key => 
                scores[key] !== undefined && scores[key] !== null
            );
            
            // Only proceed if we have metrics to display
            if (filteredBreakdownKeys.length === 0) {
                containerElement.innerHTML = '<p style="text-align:center;">No data available for visualization. Please fill out at least one question.</p>';
                return;
            }

            const canvas = document.createElement('canvas');
            canvas.id = 'scoreRadarChart';
            // Set minimum height for better visibility
            canvas.style.minHeight = '350px';
            canvas.style.margin = '0 auto'; // Keep centering

            // Format labels for better display - shorten or wrap long labels
            const formatLabel = (label) => {
                // Clean up labels
                let formattedLabel = label
                    .replace(/([A-Z])/g, ' $1')
                    .replace(/^./, str => str.toUpperCase())
                    .replace(' Score', '')
                    .replace('Bmi', 'BMI')
                    .replace('Whr', 'WHR');
                
                // Truncate very long labels with ellipsis for display
                if (formattedLabel.length > 15) {
                    return formattedLabel.substring(0, 13) + '...';
                }
                return formattedLabel;
            };

            // Prepare data with custom point colors based on scores - using filtered keys
            const rawScores = filteredBreakdownKeys.map(metric => scores[metric]);

            // Enhanced color gradient for points based on scores - improved for premium look
            const pointColors = rawScores.map(val => {
                if (val >= 4.5) return 'rgba(0, 180, 0, 1)';       // Excellent - Darker Green
                else if (val >= 3.5) return 'rgba(76, 187, 23, 1)'; // Good - Apple Green
                else if (val >= 3.0) return 'rgba(156, 204, 10, 1)'; // Above Average - Light Green
                else if (val >= 2.5) return 'rgba(255, 204, 0, 1)'; // Average - Apple Yellow
                else if (val >= 2.0) return 'rgba(255, 149, 0, 1)'; // Below Average - Apple Orange
                else if (val >= 1.5) return 'rgba(255, 59, 48, 1)'; // Poor - Apple Red
                else return 'rgba(215, 0, 21, 1)';                  // Very Poor - Deep Red
            });

            // Prepare data with filtered keys
            const data = {
                labels: filteredBreakdownKeys.map(metric => formatLabel(metric)),
                datasets: [{
                    label: 'Health Metrics (0–5)',
                    data: rawScores,
                    fill: true,
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderColor: 'rgba(0, 122, 255, 0.8)', // Apple blue
                    pointBackgroundColor: pointColors,
                    pointBorderColor: 'white',
                    pointBorderWidth: 1.5,
                    pointHoverBackgroundColor: '#fff',
                    pointHoverBorderColor: pointColors,
                    pointRadius: window.innerWidth <= 480 ? 4 : 5, // Increased size
                    pointHoverRadius: window.innerWidth <= 480 ? 6 : 7, // Increased hover size
                    borderWidth: 2
                }]
            };

            // Prepare config
            const config = {
                type: 'radar',
                data: data,
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    aspectRatio: 1, // Keep it square
                    layout: {
                        padding: {
                            top: 15,
                            right: 25, 
                            bottom: 15,
                            left: 25
                        }
                    },
                    animation: {
                        duration: 1000, // Animation duration in milliseconds
                        easing: 'easeOutQuart', // Easing function for smooth animation
                    },
                    scales: {
                        r: {
                            min: 0,
                            max: 5,
                            beginAtZero: true,
                            grid: { 
                                color: 'rgba(0, 0, 0, 0.08)', // Lighter grid lines
                                lineWidth: 1
                            },
                            angleLines: { 
                                color: 'rgba(0, 0, 0, 0.08)', // Lighter angle lines
                                lineWidth: 1
                            },
                            pointLabels: {
                                font: { 
                                    family: '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif', 
                                    size: window.innerWidth <= 480 ? 11 : 13, // Increased size for better readability
                                    weight: '500' // Semi-bold for better legibility
                                },
                                color: '#2C3E50',
                                padding: window.innerWidth <= 480 ? 6 : 10, // Increased padding
                                centerPointLabels: false,
                                display: true
                            },
                            ticks: {
                                stepSize: 1,
                                backdropColor: 'rgba(255, 255, 255, 0.85)', // Improved tick readability
                                backdropPadding: 3,
                                font: { 
                                    family: '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif', 
                                    size: window.innerWidth <= 480 ? 10 : 11 // Minimum 11pt as per Apple guidelines
                                },
                                color: '#636366', // Apple gray
                                showLabelBackdrop: false,
                                z: 1
                            },
                            backgroundColor: function(context) {
                                const chart = context.chart;
                                const {ctx, chartArea} = chart;
                                if (!chartArea) {
                                    return;
                                }
                                // Create gradient for the background
                                // High scores (3-5) zone with very light blue
                                const outerAreaGradient = ctx.createRadialGradient(
                                    chart.getDatasetMeta(0).data[0].x,
                                    chart.getDatasetMeta(0).data[0].y,
                                    0,
                                    chart.getDatasetMeta(0).data[0].x,
                                    chart.getDatasetMeta(0).data[0].y,
                                    chart.scales.r.drawingArea
                                );
                                outerAreaGradient.addColorStop(0.6, 'rgba(255, 255, 255, 0)');
                                outerAreaGradient.addColorStop(1, 'rgba(239, 246, 255, 0.3)');
                                return outerAreaGradient;
                            }
                        }
                    },
                    plugins: {
                        legend: { 
                            display: true,
                            position: 'bottom',
                            labels: {
                                boxWidth: 20,
                                padding: window.innerWidth <= 480 ? 10 : 15,
                                font: {
                                    size: window.innerWidth <= 480 ? 11 : 13 // Minimum 11pt as per Apple guidelines
                                }
                            }
                        },
                        tooltip: {
                            enabled: true,
                            backgroundColor: 'rgba(44, 62, 80, 0.9)', // More opaque for better visibility
                            titleFont: { 
                                family: '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif', 
                                size: window.innerWidth <= 480 ? 12 : 13
                            },
                            bodyFont: { 
                                family: '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif', 
                                size: window.innerWidth <= 480 ? 11 : 12
                            },
                            padding: 10, // Increased padding
                            cornerRadius: 6, // More rounded corners for Apple-like UI
                            callbacks: {
                                label: function(context) {
                                    const score = context.raw;
                                    if (score === null) return 'No data';
                                    
                                    let status = '';
                                    if (score >= 4.5) status = ' (Excellent)';
                                    else if (score >= 3.5) status = ' (Good)';
                                    else if (score >= 2.5) status = ' (Average)';
                                    else if (score >= 1.5) status = ' (Below Average)';
                                    else status = ' (Poor)';
                                    
                                    return `Score: ${score}${status}`;
                                },
                                title: function(tooltipItems) {
                                    const dataIndex = tooltipItems[0].dataIndex;
                                    const originalMetric = filteredBreakdownKeys[dataIndex];
                                    return originalMetric
                                        .replace(/([A-Z])/g, ' $1')
                                        .replace(/^./, str => str.toUpperCase())
                                        .replace(' Score', '')
                                        .replace('Bmi', 'BMI')
                                        .replace('Whr', 'WHR');
                                }
                            }
                        }
                    }
                }
            };

            // Render chart (removing title as requested)
            containerElement.innerHTML = ''; // Clear previous content
            
            // Update canvas size for better visibility
            canvas.style.minHeight = '400px'; // Increased from 350px
            canvas.style.margin = '0 auto';
            
            containerElement.appendChild(canvas);
            const chartInstance = new Chart(canvas, config);
            debug("Score Radar Chart rendered.");
            
            // Create fallback image for print
            try {
                setTimeout(() => {
                    // Delay creating fallback to ensure chart is fully rendered
                    if (chartInstance) {
                        const image = chartInstance.toBase64Image('image/png', 1.0);
                        const fallbackDiv = document.createElement('div');
                        fallbackDiv.id = 'scoreChartFallbackImage';
                        fallbackDiv.className = 'chart-fallback';
                        fallbackDiv.innerHTML = `<img src="${image}" class="chart-print-image" alt="Health Metrics Radar Chart">`;
                        containerElement.appendChild(fallbackDiv);
                    }
                }, 500);
            } catch (e) {
                console.log("Error creating chart fallback image:", e);
            }
        }


        /**
         * Creates the Factor Impact Bar Chart.
         * @param {object} scores - Object containing user scores.
         * @param {object} weights - Object containing metric weights.
         * @param {array} breakdownKeys - Array of metric keys.
         * @param {HTMLElement} containerElement - The DOM element to render the chart in.
         */
        function createImpactBarChart(scores, weights, breakdownKeys, containerElement) {
             debug("Creating Factor Impact Bar Chart...");
             if (!window.Chart) {
                 console.error("Chart.js not loaded!");
                 containerElement.innerHTML = '<p style="text-align:center; color: red;">Chart library not loaded.</p>';
                 return;
             }

             // Filter breakdownKeys to only include metrics that have values in the scores object
             const filteredBreakdownKeys = breakdownKeys.filter(key => 
                 scores[key] !== undefined && scores[key] !== null
             );
             
             // Only proceed if we have metrics to display
             if (filteredBreakdownKeys.length === 0) {
                 containerElement.innerHTML = '<p style="text-align:center;">No data available for visualization. Please fill out at least one question.</p>';
                 return;
             }

             const canvas = document.createElement('canvas');
             canvas.id = 'impactBarChart';
             canvas.style.maxHeight = '450px'; // Adjust height for bar chart

             // Calculate impact values (difference from baseline 3)
             const diffs = filteredBreakdownKeys.map(key => {
                 const score = scores[key];
                 const weight = weights[key];
                 if (typeof score === 'number' && !isNaN(score) && typeof weight === 'number') {
                     // Impact = weight * (score - 3). Positive impact means better than average (reduces age).
                     return weight * (score - 3);
                 }
                 return 0; // Use 0 for missing weights (shouldn't happen with filtered keys)
             });
             
             // ===== BEGIN VISUAL SCALING FACTOR =====
             // This scaling is applied ONLY for visual display in the chart, not for actual calculations
             // IMPORTANT: You can adjust this scaling factor to make the bars more visible
             const visualScalingFactor = 8.0; // Adjust this value as needed
             
             // Create a copy of the diffs array with the visual scaling applied
             // Original values are preserved in 'diffs' array for tooltips and other calculations
             const scaledDiffs = diffs.map(value => value * visualScalingFactor);
             
             // Store original values for tooltip display
             const originalDiffs = [...diffs];
             // ===== END VISUAL SCALING FACTOR =====

             // Generate colors based on impact value (using original diffs, not scaled)
             const barColors = diffs.map(value => {
                 if (value > 0) {
                     // Positive impact (good) - green gradient based on magnitude
                     const intensity = Math.min(1, value / 1.0); // Scale intensity based on impact 
                     return `rgba(0, ${Math.floor(150 + 90 * intensity)}, 0, 0.8)`;
                 } else if (value < 0) {
                     // Negative impact (bad) - red gradient based on magnitude
                     const intensity = Math.min(1, Math.abs(value) / 1.0); // Scale intensity based on impact
                     return `rgba(${Math.floor(150 + 90 * intensity)}, 0, 0, 0.8)`;
                 }
                 // Neutral (exactly average) - gray
                 return 'rgba(150, 150, 150, 0.8)';
             });

             // Format labels for better display
             const labels = filteredBreakdownKeys.map(metric =>
                 (metric.replace(/([A-Z])/g, ' $1').replace(/^./, str => str.toUpperCase()))
                 .replace(' Score', '')
                 .replace('Bmi', 'BMI')
                 .replace('Whr', 'WHR')
             );

             // Determine optimal Y axis range for the scaled values
             let minVal = Math.min(...scaledDiffs.filter(d => d !== null));
             let maxVal = Math.max(...scaledDiffs.filter(d => d !== null));
             
             // Add some padding to the y-axis range
             let yMin = Math.min(-4, (minVal < -4) ? minVal - 0.5 : -4);
             let yMax = Math.max(6, (maxVal > 6) ? maxVal + 0.5 : 6);
             
             // If all values are very small, ensure the chart still has a reasonable scale
             if (Math.abs(maxVal) < 1 && Math.abs(minVal) < 1) {
                 yMin = -4;
                 yMax = 6;
             }

             // Prepare data with colored bars based on impact - using SCALED values for display
             const data = {
                 labels: labels,
                 datasets: [{
                     label: 'Health Score Deviation',
                     data: scaledDiffs, // Use the scaled values for the bars
                     backgroundColor: barColors,
                     borderColor: barColors.map(color => color.replace('0.8', '1')),
                     borderWidth: 1,
                     borderRadius: 4, // Slightly rounded corners
                     barPercentage: 0.7,
                     categoryPercentage: 0.85
                 }]
             };

             // Sort the data for better visualization (optional)
             const sortByImpact = false; // Set to true to enable sorting
             if (sortByImpact) {
                 // Create a combined array of [label, diff, color] for sorting
                 const combined = labels.map((label, i) => ({
                     label: label,
                     scaledDiff: scaledDiffs[i],
                     originalDiff: originalDiffs[i], // Keep track of original values
                     color: barColors[i]
                 }));
                 
                 // Sort by impact value (descending)
                 combined.sort((a, b) => b.scaledDiff - a.scaledDiff);
                 
                 // Reassign sorted values
                 data.labels = combined.map(item => item.label);
                 data.datasets[0].data = combined.map(item => item.scaledDiff);
                 data.datasets[0].backgroundColor = combined.map(item => item.color);
                 data.datasets[0].borderColor = combined.map(item => item.color.replace('0.8', '1'));
                 
                 // Update originalDiffs array order to match the sorted order
                 originalDiffs.length = 0;
                 combined.forEach(item => originalDiffs.push(item.originalDiff));
             }

             // Prepare enhanced config
             const config = {
                 type: 'bar',
                 data: data,
                 options: {
                     responsive: true,
                     maintainAspectRatio: false, // Allow flexible height
                     indexAxis: 'x', // Vertical bars
                     scales: {
                         y: {
                             min: yMin, 
                             max: yMax,
                             grid: {
                                 color: 'rgba(0, 0, 0, 0.05)'
                             },
                             ticks: {
                                 font: {
                                     family: '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif',
                                     size: window.innerWidth <= 480 ? 10 : 11
                                 },
                                 color: '#86868b',
                                 callback: function(value) {
                                     // Display simplified tick values. These are visually scaled but
                                     // we don't need to indicate that on the axis
                                     return value.toFixed(0);
                                 }
                             },
                             title: {
                                 display: true,
                                 text: 'Impact on Biological Age (years)',
                                 font: {
                                     family: '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif',
                                     size: window.innerWidth <= 480 ? 11 : 12,
                                     weight: 'bold'
                                 },
                                 color: '#2C3E50',
                                 padding: {top: 0, bottom: 10}
                             }
                         },
                         x: {
                             grid: { display: false }, // Cleaner X-axis
                             ticks: {
                                 font: { 
                                     family: '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif', 
                                     size: window.innerWidth <= 480 ? 9 : 11
                                 },
                                 color: '#1d1d1f',
                                 maxRotation: 45, // Allow rotation to prevent overlap
                                 minRotation: window.innerWidth <= 480 ? 45 : 0,
                                 autoSkip: true,
                                 maxTicksLimit: window.innerWidth <= 480 ? 8 : 12
                             }
                         }
                     },
                     plugins: {
                         legend: { 
                             display: false // Hide legend
                         },
                         tooltip: {
                             enabled: true,
                             backgroundColor: 'rgba(0, 0, 0, 0.8)',
                             titleFont: { 
                                 family: '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif', 
                                 size: window.innerWidth <= 480 ? 12 : 13 
                             },
                             bodyFont: { 
                                 family: '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif', 
                                 size: window.innerWidth <= 480 ? 11 : 12 
                             },
                             padding: 10,
                             callbacks: {
                                 title: function(context) {
                                     return context[0].label;
                                 },
                                 label: function(context) {
                                     // Use the index of the hovered bar to get the ORIGINAL (unscaled) value
                                     const index = context.dataIndex;
                                     const originalValue = originalDiffs[index];
                                     
                                     if (originalValue === null) return 'No data';
                                     
                                     // Format impact message based on original (unscaled) value
                                     if (originalValue > 0) {
                                         return `Reduces biological age by ${originalValue.toFixed(2)} years`;
                                     } else if (originalValue < 0) {
                                         return `Increases biological age by ${Math.abs(originalValue).toFixed(2)} years`;
                                     } else {
                                         return 'No impact on biological age';
                                     }
                                 }
                             }
                         }
                     }
                 }
             };

             // Add explanatory subtitle below title
             const title = document.createElement('h4');
             title.className = 'chart-title';
             title.textContent = 'Factor Impact';
             
             const subtitle = document.createElement('p');
             subtitle.className = 'chart-subtitle';
             subtitle.textContent = 'How each factor affects your biological age';
             subtitle.style.textAlign = 'center';
             subtitle.style.fontSize = '14px';
             subtitle.style.marginTop = '5px';
             subtitle.style.color = '#5a5a5a';
             
             // Create zero line annotation to emphasize positive/negative boundary
             config.options.plugins.annotation = {
                 annotations: {
                     zeroLine: {
                         type: 'line',
                         yMin: 0,
                         yMax: 0,
                         borderColor: 'rgba(0, 0, 0, 0.3)',
                         borderWidth: 1,
                         borderDash: [4, 4],
                     }
                 }
             };
             
             containerElement.innerHTML = ''; // Clear previous content
             containerElement.appendChild(title);
             containerElement.appendChild(subtitle);
             containerElement.appendChild(canvas);
             
             // Create a legend to explain colors with note about visual scaling
             const legend = document.createElement('div');
             legend.className = 'chart-custom-legend';
             legend.style.display = 'flex';
             legend.style.justifyContent = 'center';
             legend.style.alignItems = 'center';
             legend.style.marginTop = '15px';
             legend.style.fontSize = '13px';
             legend.innerHTML = `
                <div style="display: flex; align-items: center; margin-right: 20px;">
                    <span style="display: inline-block; width: 12px; height: 12px; background-color: rgba(0, 200, 0, 0.8); margin-right: 5px;"></span>
                    <span>Reduces age</span>
                </div>
                <div style="display: flex; align-items: center;">
                    <span style="display: inline-block; width: 12px; height: 12px; background-color: rgba(200, 0, 0, 0.8); margin-right: 5px;"></span>
                    <span>Increases age</span>
                </div>
             `;
             
             // Add note about visual scaling
             const scalingNote = document.createElement('div');
             scalingNote.style.fontSize = '11px';
             scalingNote.style.color = '#888';
             scalingNote.style.textAlign = 'center';
             scalingNote.style.marginTop = '5px';
             scalingNote.innerHTML = `<i>Note: Chart bars are visually scaled for better readability. Tooltips show actual values.</i>`;
             legend.appendChild(scalingNote);
             
             containerElement.appendChild(legend);
             
             // Initialize Chart with annotation plugin if available
             if (window.Chart && (window.ChartAnnotation || typeof Chart.Annotation !== 'undefined')) {
                 new Chart(canvas, config);
             } else {
                 // If annotation plugin not available, remove it from the config
                 delete config.options.plugins.annotation;
                 new Chart(canvas, config);
             }
             
             debug("Factor Impact Bar Chart rendered with visual scaling factor: " + visualScalingFactor);
             
             // Create fallback image for print
             try {
                 setTimeout(() => {
                     // Delay creating fallback to ensure chart is fully rendered
                     const chartInstance = Chart.getChart(canvas);
                     if (chartInstance) {
                         chartInstance.toBase64Image('image/png', 1.0).then(image => {
                             const fallbackDiv = document.createElement('div');
                             fallbackDiv.id = 'impactChartFallbackImage';
                             fallbackDiv.className = 'chart-fallback';
                             fallbackDiv.innerHTML = `<img src="${image}" class="chart-print-image" alt="Factor Impact Chart">`;
                             containerElement.appendChild(fallbackDiv);
                         });
                     }
                 }, 500);
             } catch (e) {
                 console.log("Error creating chart fallback image:", e);
             }
        }

        // --- Display & Form Handling ---

    })(jQuery); // Pass jQuery to the closure to use the `$` alias safely
    </script>
    <?php
    return ob_get_clean();
}
add_shortcode('longevity_form', 'longevity_assessment_form');

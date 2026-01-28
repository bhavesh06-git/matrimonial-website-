<?php require_once 'includes/header.php'; ?>
    
    <!-- Google Fonts -->

<!-- Hero Section -->

<section class="hero-section text-center">
    <div class="container">
        <div class="row justify-content-center">
           
            <div class="col-lg-10">
                
                <h1 class="display-3" data-aos="fade-down">Begin Your Journey to a Blessed Union</h1>
                <p class="lead" data-aos="fade-up" data-aos-delay="100">
                    SoulMate is the most trusted platform for finding a life partner. We unite hearts with a blend of tradition and technology.
                </p>
                
                <!-- Quick Search Form -->
                <div class="quick-search-form" data-aos="fade-up" data-aos-delay="200">
                    <form class="row g-3 align-items-end" action="search.php" method="GET">
                        <div class="col-md">
                            <label for="gender" class="form-label">I'm looking for a</label>
                            <select id="gender" name="gender" class="form-select">
                                <option value="Female">Bride</option>
                                <option value="Male">Groom</option>
                            </select>
                        </div>
                        <div class="col-md">
                            <label for="religion" class="form-label">Religion</label>
                            <input type="text" id="religion" name="religion" class="form-control" placeholder="e.g. Hindu">
                        </div>
                        <div class="col-md">
                            <label for="country" class="form-label">Country</label>
                            <input type="text" id="country" name="country" class="form-control" placeholder="e.g. India">
                        </div>
                        <div class="col-md-auto">
                            <button type="submit" class="btn btn-primary-custom w-100">Let's Begin</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- How It Works Section -->
<section class="how-it-works-section">
    <div class="container">
        <div class="text-center">
            <h2 class="section-title" data-aos="fade-up">Find Your Partner in 3 Simple Steps</h2>
            <p class="section-subtitle" data-aos="fade-up" data-aos-delay="100">Follow our straightforward process to get closer to your soulmate.</p>
        </div>
        <div class="row">
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                <div class="step-card">
                    <div class="icon-circle"><i class="fas fa-user-plus"></i></div>
                    <h5>Create Your Profile</h5>
                    <p>Register for free and build a detailed profile to express your personality and partner preferences.</p>
                </div>
            </div>
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="300">
                <div class="step-card">
                    <div class="icon-circle"><i class="fas fa-search"></i></div>
                    <h5>Search & Connect</h5>
                    <p>Use our advanced search filters to find members who match your criteria and connect with them.</p>
                </div>
            </div>
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="400">
                <div class="step-card">
                    <div class="icon-circle"><i class="fas fa-heart"></i></div>
                    <h5>Start Your Journey</h5>
                    <p>Take the conversation forward and begin the beautiful journey of a lifetime together.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Why Choose Us Section -->
<section class="why-choose-us-section">
    <div class="container">
        <div class="text-center">
            <h2 class="section-title" data-aos="fade-up">Why SoulMate is the Right Choice</h2>
            <p class="section-subtitle" data-aos="fade-up" data-aos-delay="100">We are committed to providing a safe, secure, and successful matchmaking experience.</p>
        </div>
        <div class="row g-4">
            <div class="col-lg-4" data-aos="fade-up" data-aos-delay="200">
                <div class="feature-card">
                    <div class="feature-icon"><i class="fas fa-user-shield"></i></div>
                    <h4>100% Verified Profiles</h4>
                    <p>Every profile is manually screened by our team to ensure authenticity and build a trustworthy community.</p>
                </div>
            </div>
            <div class="col-lg-4" data-aos="fade-up" data-aos-delay="300">
                <div class="feature-card">
                    <div class="feature-icon"><i class="fas fa-lock"></i></div>
                    <h4>Strict Privacy Control</h4>
                    <p>You are in control. Manage your privacy settings and choose who can see your photos and contact details.</p>
                </div>
            </div>
            <div class="col-lg-4" data-aos="fade-up" data-aos-delay="400">
                <div class="feature-card">
                    <div class="feature-icon"><i class="fas fa-comments"></i></div>
                    <h4>Advanced Search Filters</h4>
                    <p>Our powerful filters help you find the most compatible matches based on location, education, community and more.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Featured Profiles Section -->
<section class="featured-profiles-section">
    <div class="container">
        <div class="text-center">
            <h2 class="section-title" data-aos="fade-up">Featured Profiles</h2>
            <p class="section-subtitle" data-aos="fade-up" data-aos-delay="100">Meet some of our members who are looking for their special someone.</p>
        </div>
        <div class="row">
            <?php
            // Fetch a few random profiles to feature
            $result = $conn->query("SELECT * FROM users WHERE profile_status = 'Active' AND profile_image != 'default.png' ORDER BY RAND() LIMIT 3");
            if ($result && $result->num_rows > 0) {
                while ($profile = $result->fetch_assoc()) {
                    include 'includes/profile_card.php';
                }
            } else {
                echo '<p class="text-center col-12">No featured profiles available at the moment.</p>';
            }
            ?>
        </div>
    </div>
</section>

<!-- Success Stories Section -->
<section class="success-stories-section">
    <div class="container">
        <div class="text-center">
            <h2 class="section-title" data-aos="fade-up">Our Success Stories</h2>
            <p class="section-subtitle" data-aos="fade-up" data-aos-delay="100">Thousands have found their life partner through SoulMate. Here are a few of them.</p>
        </div>
        <div class="row g-4">
            <div class="col-lg-4" data-aos="fade-up" data-aos-delay="200">
                <div class="story-card">
                    <img src="https://img.etimg.com/thumb/width-420,height-315,imgsize-43812,resizemode-75,msid-113370229/tech/technology/user-success-stories-shaadi-com-changing-lives-one-match-at-a-time/li.jpg" alt="Happy Couple">
                    <div class="story-card-content">
                        <p>"We found each other thanks to SoulMate. The platform is so genuine and easy to use. We are happily married now and couldn't be more grateful!"</p>
                        <span class="couple-name">- Priya & Rohan</span>
                    </div>
                </div>
            </div>
            <div class="col-lg-4" data-aos="fade-up" data-aos-delay="300">
                <div class="story-card">
                    <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSP2Zrr43jSzc_5de1C0XNb4SqSog2c7i_-nRTeL8cb32prFIP3KMqotRbOGUSeb-K80TE&usqp=CAU" alt="Happy Couple">
                     <div class="story-card-content">
                        <p>"Finding someone who shares your values is tough, but SoulMate made it possible. We connected instantly. Highly recommended for anyone serious about marriage."</p>
                        <span class="couple-name">- Anjali & Sameer</span>
                    </div>
                </div>
            </div>
            <div class="col-lg-4" data-aos="fade-up" data-aos-delay="400">
                 <div class="story-card">
                    <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcS9mMSfm88p10ALG7Hb4pjtz0ltEb5CFGV20pZ1HBTPtZxl5or7ocHiePwus9qDl4IMv78&usqp=CAU" alt="Happy Couple">
                     <div class="story-card-content">
                        <p>"I was about to give up on finding a partner online, but a friend recommended SoulMate. Best decision ever! I found my wonderful husband here."</p>
                        <span class="couple-name">- Meera & Vikram</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="cta-section">
    <div class="container" data-aos="fade-up">
        <h2>Your Story is Waiting to Happen</h2>
        <p class="my-4">Join thousands of others who have found their true love. Register for free today and take the first step towards your happy future.</p>
        <a href="register.php" class="btn btn-light-custom btn-lg">Create Your Profile Now</a>
    </div>
</section>



<?php require_once 'includes/footer.php'; ?>

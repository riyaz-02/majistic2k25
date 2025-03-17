<?php
// Define your sponsor logos array
$upper_sponsors = [
    ['image' => 'https://i.postimg.cc/L5bm8Kz5/2-1.png', 'alt' => 'Sponsor 1', 'url' => 'https://sponsor1.com'],
    ['image' => 'https://i.postimg.cc/DZ52Rb0q/1-1.png', 'alt' => 'Sponsor 2', 'url' => 'https://sponsor2.com'],
    ['image' => 'https://i.postimg.cc/SKfVf1q3/10.png', 'alt' => 'Sponsor 3', 'url' => 'https://sponsor3.com'],
    ['image' => 'https://i.postimg.cc/nh73CcWH/7.png', 'alt' => 'Sponsor 4', 'url' => 'https://sponsor4.com'],
    ['image' => 'https://i.postimg.cc/L5nM7Crp/5-1.png', 'alt' => 'Sponsor 5', 'url' => 'https://sponsor5.com'],
    ['image' => 'https://i.postimg.cc/LX4DCk8X/9.png', 'alt' => 'Sponsor 5', 'url' => 'https://sponsor5.com'],
    ['image' => 'https://i.postimg.cc/Gm017jKW/6-1.png', 'alt' => 'Sponsor 5', 'url' => 'https://sponsor5.com'],
    ['image' => 'https://i.postimg.cc/Pr0tGHXF/3-1.png', 'alt' => 'Sponsor 5', 'url' => 'https://sponsor5.com'],
    ['image' => 'https://i.postimg.cc/bvh3bfX1/8.png', 'alt' => 'Sponsor 5', 'url' => 'https://sponsor5.com'],
    // Add more sponsors as needed
];

$lower_sponsors = [
    ['image' => 'https://i.postimg.cc/Pr0tGHXF/3-1.png', 'alt' => 'Sponsor 5', 'url' => 'https://sponsor5.com'],
    ['image' => 'https://i.postimg.cc/L5bm8Kz5/2-1.png', 'alt' => 'Sponsor 1', 'url' => 'https://sponsor1.com'],
    ['image' => 'https://i.postimg.cc/DZ52Rb0q/1-1.png', 'alt' => 'Sponsor 2', 'url' => 'https://sponsor2.com'],
    ['image' => 'https://i.postimg.cc/LX4DCk8X/9.png', 'alt' => 'Sponsor 5', 'url' => 'https://sponsor5.com'],
    ['image' => 'https://i.postimg.cc/bvh3bfX1/8.png', 'alt' => 'Sponsor 5', 'url' => 'https://sponsor5.com'],
    ['image' => 'https://i.postimg.cc/nh73CcWH/7.png', 'alt' => 'Sponsor 4', 'url' => 'https://sponsor4.com'],
    ['image' => 'https://i.postimg.cc/SKfVf1q3/10.png', 'alt' => 'Sponsor 3', 'url' => 'https://sponsor3.com'],
    ['image' => 'https://i.postimg.cc/L5nM7Crp/5-1.png', 'alt' => 'Sponsor 5', 'url' => 'https://sponsor5.com'],
    ['image' => 'https://i.postimg.cc/Gm017jKW/6-1.png', 'alt' => 'Sponsor 5', 'url' => 'https://sponsor5.com'],
    // Add more sponsors as needed
];
?>

<section class="sponsors-section">
    <div class="container">
        
        <!-- Upper marquee (left to right) -->
        <div class="marquee-container">
            <div class="marquee marquee-ltr">
                <div class="marquee-content">
                    <?php foreach($upper_sponsors as $sponsor): ?>
                        <div class="sponsor-item">
                            <img src="<?php echo $sponsor['image']; ?>" alt="<?php echo $sponsor['alt']; ?>" class="sponsor-logo">
                        </div>
                    <?php endforeach; ?>
                </div>
                <!-- Duplicate content for infinite scrolling -->
                <div class="marquee-content">
                    <?php foreach($upper_sponsors as $sponsor): ?>
                        <div class="sponsor-item">
                            <img src="<?php echo $sponsor['image']; ?>" alt="<?php echo $sponsor['alt']; ?>" class="sponsor-logo">
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        
        <!-- Lower marquee (right to left) -->
        <div class="marquee-container">
            <div class="marquee marquee-rtl">
                <div class="marquee-content">
                    <?php foreach($lower_sponsors as $sponsor): ?>
                        <div class="sponsor-item">
                            <img src="<?php echo $sponsor['image']; ?>" alt="<?php echo $sponsor['alt']; ?>" class="sponsor-logo">
                        </div>
                    <?php endforeach; ?>
                </div>
                <!-- Duplicate content for infinite scrolling -->
                <div class="marquee-content">
                    <?php foreach($lower_sponsors as $sponsor): ?>
                        <div class="sponsor-item">
                            <img src="<?php echo $sponsor['image']; ?>" alt="<?php echo $sponsor['alt']; ?>" class="sponsor-logo">
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</section>
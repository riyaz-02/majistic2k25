<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        .preloader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: #000;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }

        .logo-container {
            position: relative;
            width: 50%;
            max-width: 600px;
            min-width: 200px;
            margin: 0 auto;
            animation: pulse 2s infinite;
        }

        .logo {
            width: 100%;
            height: auto;
            max-width: 100%;
            opacity: 0;
            animation: zoomOut 1s ease-in forwards;
        }

        .loading-bar {
            width: 200px;
            height: 2px;
            background: rgba(255, 255, 255, 0.1);
            margin-top: 50px;
            position: relative;
            overflow: hidden;
            opacity: 0;
            animation: fadeIn 1s ease-in forwards 0.8s;
        }

        .loading-progress {
            position: absolute;
            top: 0;
            left: 0;
            height: 100%;
            width: 0;
            background: white;
            animation: progress 3s ease-in-out infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.03); }
            100% { transform: scale(1); }
        }

        @keyframes fadeIn {
            0% { opacity: 0; }
            100% { opacity: 1; }
        }

        @keyframes progress {
            0% { width: 0; }
            50% { width: 100%; }
            100% { width: 0; }
        }

        @keyframes zoomOut {
            0% {
                opacity: 0;
                transform: scale(3);
            }
            100% {
                opacity: 1;
                transform: scale(1);
            }
        }

        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.03);
            }
        }

        @keyframes zoomInOut {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.05);
            }
        }

        /* Responsive styles */
        @media (max-width: 768px) {
            .logo-container {
                width: 60%;
                max-width: 400px;
            }

            .loading-bar {
                width: 150px;
            }
        }

        @media (max-width: 480px) {
            .logo-container {
                width: 70%;
                max-width: 300px;
            }

            .loading-bar {
                width: 100px;
            }
        }

    </style>
</head>
<body>
    <div class="preloader">
        <div class="logo-container">
        <img src="https://i.ibb.co/s9pJ72SV/majisticlogo.png" alt="Majistic Logo" class="logo">        
        </div>
        <div class="loading-bar">
            <div class="loading-progress"></div>
        </div>
    </div>

    <script>
        // Hide preloader after a set time (5 seconds) regardless of page load status
        const maxPreloaderTime = 6000; // 5 seconds maximum preloader display time
        
        // Function to hide preloader
        function hidePreloader() {
            const preloader = document.querySelector('.preloader');
            preloader.style.transition = 'opacity 0.5s ease';
            preloader.style.opacity = '0';
            setTimeout(() => {
                preloader.style.display = 'none';
            }, 3500);
        }

        // Set maximum time for preloader display
        const preloaderTimeout = setTimeout(hidePreloader, maxPreloaderTime);
        
        // Add this to your PHP file to hide preloader after page loads (as fallback)
        window.addEventListener('load', function() {
            clearTimeout(preloaderTimeout); // Clear the timeout if page loads before max time
            hidePreloader();
        });
    </script>
</body>
</html>




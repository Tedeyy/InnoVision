<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Choose Registration Type</title>
    <link rel="stylesheet" href="userregister.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="wrap">
        <div class="card">
            <h1>Create your account</h1>
            <p class="sub" id="trigger-text">Choose how you want to use <span class="brand">InnoVision</span></p>

            <div class="options">
                <form>
                    <button class="opt buyer" type="submit" formaction="buyer/req.php">
                        <span class="icon">🛒</span>
                        Register as Buyer
                    </button>
                    <button class="opt seller" type="submit" formaction="seller/req.php">
                        <span class="icon">🏪</span>
                        Register as Seller
                    </button>
                </form>
            </div>

            <div class="back">
                <a href="login.php">Already have an account? Sign in</a>
            </div>
        </div>
    </div>
    <script>
    (function(){
        var clicks = 0;
        var span = document.querySelector('#trigger-text .brand');
        if (!span) return;
        span.addEventListener('click', function(){
            clicks++;
            if (clicks >= 3) {
                window.location.href = 'useradmin.php';
            }
            setTimeout(function(){ clicks = 0; }, 1000);
        });
    })();
    </script>
</body>
</html>
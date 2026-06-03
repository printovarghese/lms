<!DOCTYPE html>
<html>
<head>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <style>
        body {
            background-color: #454545;
        }
    </style>
</head>
<body>
<section class="vh-100 gradient-custom">
  <div class="container py-5 h-100">
    <div class="row d-flex justify-content-center align-items-center h-100">
      <div class="col-12 col-md-8 col-lg-6 col-xl-5">
      <div class="card text-white" style="border-radius: 1rem; background-color: #ee5057;">

          <div class="card-body p-5 text-center">

            <div class="mb-md-5 mt-md-4 pb-5">
                <form action="Laundry/login" method="post">
                    <h2 class="fw-bold mb-2 text-uppercase">Login</h2>
                    <p class="text-white-50 mb-5">Please enter your login and password!</p>

                    <div class="form-outline form-white mb-4">
                        <input type="text" id="admno" name="admno" class="form-control form-control-lg" />
                        <label class="form-label" for="typeEmailX">User Name</label>
                    </div>

                    <div class="form-outline form-white mb-4">
                        <input type="password" id="typePasswordX" name="password" class="form-control form-control-lg" />
                        <label class="form-label" for="typePasswordX">Password</label>
                    </div>

                    <button class="btn btn-outline-light btn-lg px-5" type="submit">Login</button>

                </form>

            </div>

            <!-- <div>
              <p class="mb-0">Don't have an account? <a href="#!" class="text-white-50 fw-bold">Sign Up</a>
              </p>
            </div> -->

          </div>
        </div>
      </div>
    </div>
  </div>
</section>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
</body>
</html>
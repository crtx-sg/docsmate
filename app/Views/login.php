<link rel="stylesheet" href="/assets/css/login-style.css?1.1" />

 <div class="container">
      <div class="forms-container">
        <div class="signin-signup">
          <form action="/" class="sign-in-form" method="post">
            <?php if (isset($validation)): ?>
            <div class="col-12" style="z-index: 100;position:absolute;width:50%;top:-100px;">
              <div class="alert alert-danger" role="alert">
                <?= $validation->listErrors() ?>
              </div>
            </div>
            <?php endif; ?>
            <h2 class="title">Sign in</h2>
            <div class="input-field">
              <i class="fas fa-user"></i>
              <input type="text" placeholder="Email" class="no-autofill-bkg"  name="email" id="email" value="<?= set_value('email') ?>">
         
              <!-- <input type="text" placeholder="Username" /> -->
            </div>
            <div class="input-field">
              <i class="fas fa-lock"></i>
              <input type="password" placeholder="Password" class="no-autofill-bkg"  name="password" id="password" value="" />
            </div>
            <input type="submit" value="Login" class="btn solid" />
          </form>
          <div class="h-100 d-flex align-items-center justify-content-center">
          <label class = "font-weight-bold text-muted" for="forgotPassword">Forgot Password</label>
          <a title="Forgot Password" onclick="verifyEmail()">
            <span class="fa-passwd-reset fa-stack">
                <i class="fa fa-undo fa-stack-2x"></i>
                <i class="fa fa-lock fa-stack-1x"></i>
            </span>
                    </a>
            </div>
        </div>
      </div>

      <div class="panels-container">
        <div class="panel left-panel">
          <figure>
          <img style="margin:0 auto" src="https://docsgo.company.in/Docsgo-Logo.png" class="image" alt="" />
          <figcaption><h3>Project Document Management System</h3></figcaption>
          </figure>
        </div>
      </div>
    </div>

    <?php if (session()->get('validityMessage')): ?>
    <footer class='website-footer'>
        <p><?= session()->get('validityMessage') ?></p>
    </footer>
<?php endif; ?>
<footer class='website-footer' style="background:#e6e6ff">
<p style="color:#4d4dff;">DocsGo <?php echo getenv('app.version')." "; ?><i class="fa fa-copyright"></i> 2023 VMI Software R&D. All rights reserved.</p>
</footer>
<script>
  function verifyEmail() {
        var formTitle = "Forgot Password",
            buttonText = "ForgotPwd";

        var dialog = bootbox.dialog({
            title: formTitle,
            size: 'medium',
            message: `<form id="passwordForm">
                        <div class="row justify-content-center">
                        <div class="col-12">
                                <div class="form-group">
                                    <label class = "font-weight-bold text-muted" for="emailId">Email</label>
                                    <input type="text" class="form-control" name="emailId" id="emailId" placeholder="Email" />
                                </div>
                            </div>
                            </div>
                        </form>                       
                        `,
                        buttons: {
                          cancel: {
                    label: "Cancel",
                    className: 'btn-secondary'
                },
                ok: {
                    label: buttonText,
                    className: "btn-primary",
                    callback: function() {

                        const emailId = $('#emailId').val();
                        let passwordForm = new FormData(document.getElementById("passwordForm"));
                        if (emailId != "") {
                          const object = {
                                        id: emailId
                                    }
                            validateEmail('/validateEmail', object);
                        } else {
                            showPopUp("Validation Error", "Email cannot be empty!")
                        }

                    }
                }

            }
        });
  }

function validateEmail(url, req) {
    makePOSTRequest(url, req)
        .then((data) => {
            if (data.success == "True") {
            var formTitle = "Reset Password",
            buttonText = "ResetPwd";

        var dialog = bootbox.dialog({
            title: formTitle,
            size: 'large',
            message: `          
                    <form id="resetPwdForm" method="post">
                        <div class="row justify-content-center">
                        <div class="row">
                          <div class="col-12 col-sm-6">
                          <div class="form-group">
                          <label for="fpassword">Password</label>
                          <input type="password" class="form-control" name="fpassword" id="fpassword" value="">
                        </div>
                      </div>
                    <div class="col-12 col-sm-6">
                      <div class="form-group">
                        <label for="fpassword_confirm">Confirm Password</label>
                        <input type="password" class="form-control" name="fpassword_confirm" id="fpassword_confirm" value="">
                      </div>
                    </div>
                    </div>
                    </div>
                        </form>                       
                        `,
                        buttons: {
                          cancel: {
                          label: "Cancel",
                          className: 'btn-secondary'
                      },
                ok: {
                    label: buttonText,
                    className: "btn-primary",
                    callback: function() {

                        const password = $('#fpassword').val();
                        const confirmPassword = $('#fpassword_confirm').val();
                        let rstPwdForm = new FormData(document.getElementById("resetPwdForm"));
                        if (password != "" && resetPassword != "") {
                          const object = {
                                        id: req.id,
                                        password: password,
                                        password_confirm: confirmPassword

                                    }
                            resetPassword('/resetPassword', object);
                        } else {
                            showPopUp("Validation Error", "Password/Confirm password cannot be empty!")
                        }

                    }
                }

            }
            });
          }
         
        })
        .catch((err) => {
            console.log(err);
            showPopUp('Error', "An unexpected error occured on server.");
        })
}

function resetPassword(url, obj) {
    makePOSTRequest(url, obj)
        .then((data) => {
            if (data.success == "True") {
              showPopUp('Info', data.errorMsg);
            }
            else {
                showPopUp('Error', data.errorMsg);
            }

          })
        .catch((err) => {
            console.log(err);
            showPopUp('Error', "An unexpected error occured on server.");
        })
}

</script>

<!DOCTYPE html>

<html
  lang="en"
  class="light-style layout-navbar-fixed layout-menu-fixed"
  dir="ltr"
  data-theme="theme-default"
  data-assets-path="<?php echo  base_url(); ?>/resource/themplate-sneat-bootstrap-html-admin-template-v-1-1-1/sneat-bootstrap-html-admin-template/assets/"
  data-template="vertical-menu-template-no-customizer"
>
  <head>
    <meta charset="utf-8" />
    <meta
      name="viewport"
      content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
    />

    <title>Error</title>

    <meta name="description" content="" />

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?php echo  base_url(); ?>/resource/themplate-sneat-bootstrap-html-admin-template-v-1-1-1/sneat-bootstrap-html-admin-template/assets/img/favicon/favicon.ico" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap"
      rel="stylesheet"
    />

    <!-- Icons -->
    <link rel="stylesheet" href="<?php echo  base_url(); ?>/resource/themplate-sneat-bootstrap-html-admin-template-v-1-1-1/sneat-bootstrap-html-admin-template/assets/vendor/fonts/boxicons.css" />
    <link rel="stylesheet" href="<?php echo  base_url(); ?>/resource/themplate-sneat-bootstrap-html-admin-template-v-1-1-1/sneat-bootstrap-html-admin-template/assets/vendor/fonts/fontawesome.css" />
    <link rel="stylesheet" href="<?php echo  base_url(); ?>/resource/themplate-sneat-bootstrap-html-admin-template-v-1-1-1/sneat-bootstrap-html-admin-template/assets/vendor/fonts/flag-icons.css" />

    <!-- Core CSS -->
    <link rel="stylesheet" href="<?php echo  base_url(); ?>/resource/themplate-sneat-bootstrap-html-admin-template-v-1-1-1/sneat-bootstrap-html-admin-template/assets/vendor/css/rtl/core.css" />
    <link rel="stylesheet" href="<?php echo  base_url(); ?>/resource/themplate-sneat-bootstrap-html-admin-template-v-1-1-1/sneat-bootstrap-html-admin-template/assets/vendor/css/rtl/theme-default.css" />
    <link rel="stylesheet" href="<?php echo  base_url(); ?>/resource/themplate-sneat-bootstrap-html-admin-template-v-1-1-1/sneat-bootstrap-html-admin-template/assets/css/demo.css" />

    <!-- Vendors CSS -->
    <link rel="stylesheet" href="<?php echo  base_url(); ?>/resource/themplate-sneat-bootstrap-html-admin-template-v-1-1-1/sneat-bootstrap-html-admin-template/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />
    <link rel="stylesheet" href="<?php echo  base_url(); ?>/resource/themplate-sneat-bootstrap-html-admin-template-v-1-1-1/sneat-bootstrap-html-admin-template/assets/vendor/libs/typeahead-js/typeahead.css" />

    <!-- Page CSS -->
    <link rel="stylesheet" href="<?php echo  base_url(); ?>/resource/themplate-sneat-bootstrap-html-admin-template-v-1-1-1/sneat-bootstrap-html-admin-template/assets/vendor/css/pages/page-help-center.css" />
    <!-- Helpers -->
    <script src="<?php echo  base_url(); ?>/resource/themplate-sneat-bootstrap-html-admin-template-v-1-1-1/sneat-bootstrap-html-admin-template/assets/vendor/js/helpers.js"></script>

    <!--! Template customizer & Theme config files MUST be included after core stylesheets and helpers.js in the <head> section -->
    <!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->
    <script src="<?php echo  base_url(); ?>/resource/themplate-sneat-bootstrap-html-admin-template-v-1-1-1/sneat-bootstrap-html-admin-template/assets/js/config.js"></script>
  </head>

  <body>
    <!-- Layout wrapper -->
    <div class="layout-wrapper layout-content-navbar">
      <div class="layout-container">
      

        <!-- Layout container -->
        <div class="layout-page">
        

          <!-- Content wrapper -->
          <div class="content-wrapper">
            <!-- Content -->

            <div class="container-xxl flex-grow-1 container-p-y">
              <div class="card overflow-hidden">
                <!-- Help Center Header -->
                <div class="help-center-header d-flex flex-column justify-content-center align-items-center">
                  <h3 class="text-center">Error : <?php echo ($session == null ? ""  : $session["user"]->nickname." --> Compania : ".$session["company"]->name ); ?></h3>
                  <div class="input-wrapper my-3 input-group input-group-merge">
                   
                  
                  </div>
                  <p class="text-center px-3 mb-0"><?php echo $exception->getLine();?> <?php echo $exception->getMessage();?></p>
                </div>
                <!-- /Help Center Header -->

                <!-- Popular Articles -->
                <div class="help-center-popular-articles py-5">
                  <div class="container-xl">
                    <h4 class="text-center mt-2 mb-4">Opciones</h4>
                    <div class="row">
                      <div class="col-lg-10 mx-auto">
                        <div class="row mb-3">
                          <div class="col-md-4 mb-md-0 mb-4">
                            <div class="card border shadow-none">
                              <div class="card-body text-center">
                                <img
                                  class="mb-3"
                                  src="<?php echo  base_url(); ?>/resource/themplate-sneat-bootstrap-html-admin-template-v-1-1-1/sneat-bootstrap-html-admin-template/assets/img/icons/unicons/rocket.png"
                                  height="60"
                                  alt="Help center articles"
                                />
                                <h5>Ingresar nuevamente</h5>
                                <p>pagina de inicio de session</p>
                                <a class="btn btn-label-primary" href="<?php echo $urlLogin; ?>">Ir</a>
                              </div>
                            </div>
                          </div>

                          <div class="col-md-4 mb-md-0 mb-4">
                            <div class="card border shadow-none">
                              <div class="card-body text-center">
                                <img
                                  class="mb-3"
                                  src="<?php echo  base_url(); ?>/resource/themplate-sneat-bootstrap-html-admin-template-v-1-1-1/sneat-bootstrap-html-admin-template/assets/img/icons/unicons/cube-secondary.png"
                                  height="60"
                                  alt="Help center articles"
                                />
                                <h5>Regresar a la lista</h5>
                                <p>Ir al listado de elementos</p>
                                <a class="btn btn-label-primary" href="<?php echo $urlIndex; ?>">Ir</a>
                              </div>
                            </div>
                          </div>

                          <div class="col-md-4">
                            <div class="card border shadow-none">
                              <div class="card-body text-center">
                                <img
                                  class="mb-3"
                                  src="<?php echo  base_url(); ?>/resource/themplate-sneat-bootstrap-html-admin-template-v-1-1-1/sneat-bootstrap-html-admin-template/assets/img/icons/unicons/desktop.png"
                                  height="60"
                                  alt="Help center articles"
                                />
                                <h5>Pantalla anterior</h5>
                                <p>Regresar a la pantalla anterior</p>
                                <a class="btn btn-label-primary" href="<?php echo $urlBack; ?>">Ir</a>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <!-- /Popular Articles -->

              </div>
            </div>
            <!-- / Content -->

        
            <div class="content-backdrop fade"></div>
          </div>
          <!-- Content wrapper -->
        </div>
        <!-- / Layout page -->
      </div>

      <!-- Overlay -->
      <div class="layout-overlay layout-menu-toggle"></div>

      <!-- Drag Target Area To SlideIn Menu On Small Screens -->
      <div class="drag-target"></div>
    </div>
    <!-- / Layout wrapper -->

    <!-- Core JS -->
    <!-- build:js assets/vendor/js/core.js -->
    <script src="<?php echo  base_url(); ?>/resource/themplate-sneat-bootstrap-html-admin-template-v-1-1-1/sneat-bootstrap-html-admin-template/assets/vendor/libs/jquery/jquery.js"></script>
    <script src="<?php echo  base_url(); ?>/resource/themplate-sneat-bootstrap-html-admin-template-v-1-1-1/sneat-bootstrap-html-admin-template/assets/vendor/libs/popper/popper.js"></script>
    <script src="<?php echo  base_url(); ?>/resource/themplate-sneat-bootstrap-html-admin-template-v-1-1-1/sneat-bootstrap-html-admin-template/assets/vendor/js/bootstrap.js"></script>
    <script src="<?php echo  base_url(); ?>/resource/themplate-sneat-bootstrap-html-admin-template-v-1-1-1/sneat-bootstrap-html-admin-template/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>

    <script src="<?php echo  base_url(); ?>/resource/themplate-sneat-bootstrap-html-admin-template-v-1-1-1/sneat-bootstrap-html-admin-template/assets/vendor/libs/hammer/hammer.js"></script>
    <script src="<?php echo  base_url(); ?>/resource/themplate-sneat-bootstrap-html-admin-template-v-1-1-1/sneat-bootstrap-html-admin-template/assets/vendor/libs/i18n/i18n.js"></script>
    <script src="<?php echo  base_url(); ?>/resource/themplate-sneat-bootstrap-html-admin-template-v-1-1-1/sneat-bootstrap-html-admin-template/assets/vendor/libs/typeahead-js/typeahead.js"></script>

    <script src="<?php echo  base_url(); ?>/resource/themplate-sneat-bootstrap-html-admin-template-v-1-1-1/sneat-bootstrap-html-admin-template/assets/vendor/js/menu.js"></script>
    <!-- endbuild -->

    <!-- Vendors JS -->

    <!-- Main JS -->
    <script src="<?php echo  base_url(); ?>/resource/themplate-sneat-bootstrap-html-admin-template-v-1-1-1/sneat-bootstrap-html-admin-template/assets/js/main.js"></script>

    <!-- Page JS -->
  </body>
</html>

<!DOCTYPE html>
<html>
    <head>
        <title>
            MentorMate Migrator
        </title>

        <meta http-equiv="Content-Type" content=""; charset="" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
        <link rel="shortcut icon" type="assets/images/favicon.png" href="images/favicon.png"/>

        <link rel="stylesheet" href="assets/css/style.css">
    </head>

    <body>
        <div class="container">
            <div class="shell">
                <div class="wrapper">
                    <div class="header">
                        <div class="inner">
                            <a href="https://www.mentormate.bg/" target="_blank" class="logo">
                                <img src="assets/images/logo-scaled.png" alt="">
                            </a>

                            <h1>
                                <a href="." class="logo">
                                    WordPress <span> Migrator </span>
                                </a>
                            </h1>
                        </div>

                        <h4>
                            Migrates database to a new url with option for a new table prefix.
                        </h4>
                    </div>

                    <div class="form-content">
                        <div class="loading-screen">
                            <h5 class="loading-text">
                                This can take a while so just wait or take a coffee or whatever I am just a text, not your boss.
                            </h5>
                        </div>

                        <div class="error-box">
                        </div>

                        <form action="?" class="migrator" method="POST" enctype="multipart/form-data">
                            <div class="form-group file-field">
                                <label for="database-file">Database File: <sup>*</sup></label>

                                <span class="file-upload-replacer">
                                    Your Database File Here ( .sql file )
                                </span>

                                <input type="file" name="database-file" class="form-control-file" id="database-file" required>
                             </div>

                            <div class="form-group">
                                <label for="new-url">
                                    Migrate to: <sup>*</sup>

                                    <em> ( URL here )</em>
                                </label>

                                <input type="text" name="new-url" class="form-control" id="new-url" placeholder="http://example.com" required>
                            </div>

                            <div class="form-group">
                                <label for="prefix">
                                    New Prefix:

                                    <em>
                                        ( Optional )
                                    </em>
                                </label>

                                <input type="text" name="new-prefix" class="form-control" id="prefix" placeholder="prefix_">
                            </div>

                            <div class="form-group">
                                <div class="checkbox">
                                    <input type="checkbox" name="enable-gzip" id="gzip">

                                    <label id="checkbox-label">
                                        <span class="cr"><i class="cr-icon glyphicon glyphicon-ok"></i></span>

                                        Gzip the output ?
                                    </label>
                                </div>
                            </div>

                            <div class="form-group">
                                <button type="submit" class="btn btn-secondary">Migrate</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </body>

    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/js/functions.js"> </script>
</html>

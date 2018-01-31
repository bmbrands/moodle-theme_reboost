'use strict';

module.exports = function(grunt) {

    // Import modules.
    var path = require("path");

    // PHP strings for exec task.
    var moodleroot = path.dirname(path.dirname(__dirname));
    var dirrootopt = grunt.option("dirroot") || process.env.MOODLE_DIR || "";

    // Allow user to explicitly define Moodle root dir.
    if ("" !== dirrootopt) {
        moodleroot = path.resolve(dirrootopt);
    }

    var PWD = process.cwd();

    var decachephp = "../../admin/cli/purge_caches.php";

    grunt.initConfig({
        exec: {
            decache: {
                cmd: 'php "' + decachephp + '"',
                callback: function(error) {
                    // The exec process will output error messages.
                    // Just add one to confirm success.
                    if (!error) {
                        grunt.log.writeln("Moodle theme cache reset.");
                    }
                }
            },
            postcss: {
                command: 'npm run postcss'
            }
        },
        watch: {
            // Watch for any changes to less files and compile.
            files: ["scss/**/*.scss", "templates/*.mustache"],
            tasks: ["compile"],
            options: {
                spawn: false,
                livereload: true
            }
        },
        stylelint: {
            scss: {
                options: {
                    syntax: "scss"
                },
                src: ["scss/**/*.scss"]
            },
            css: {
                src: ["*/**/*.css"],
                options: {
                    configOverrides: {
                        rules: {
                            // These rules have to be disabled in .stylelintrc for scss compat.
                            "at-rule-no-unknown": true,
                            "no-browser-hacks": [true, {"severity": "warning"}]
                        }
                    }
                }
            }
        },
        jshint: {
            options: {
                jshintrc: true
            },
            files: ["**/amd/src/*.js"]
        },
        uglify: {
            dynamic_mappings: {
                files: grunt.file.expandMapping(
                    ["**/src/*.js", "!**/node_modules/**"],
                    "",
                    {
                        cwd: PWD,
                        rename: function(destBase, destPath) {
                            destPath = destPath.replace("src", "build");
                            destPath = destPath.replace(".js", ".min.js");
                            destPath = path.resolve(PWD, destPath);
                            return destPath;
                        }
                    }
                )
            }
        },
        sass: {
            options: {
                style: 'expanded'
            },
            dist: {
                files: {
                    'style/reboost.css': 'scss/reboost.scss'
                }
            }
        },
    });

    // Load contrib tasks.
    grunt.loadNpmTasks("grunt-contrib-watch");
    grunt.loadNpmTasks("grunt-exec");

    // Load core tasks.
    grunt.loadNpmTasks("grunt-contrib-uglify");
    grunt.loadNpmTasks("grunt-contrib-jshint");
    grunt.loadNpmTasks("grunt-sass");
    grunt.loadNpmTasks("grunt-stylelint");

    // Register CSS taks.
    grunt.registerTask("css", ["stylelint:scss", "stylelint:css"]);

    // Register tasks.
    grunt.registerTask("default", ["watch"]);
    grunt.registerTask("decache", ["exec:decache"]);

    grunt.registerTask("compile", [
        "sass",
        "exec:postcss",
        "decache"
    ]);

    grunt.registerTask("amd", ["jshint", "uglify"]);
};

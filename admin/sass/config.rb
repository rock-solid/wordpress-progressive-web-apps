# Require any additional compass plugins here.

# Get the directory that this configuration file exists in
dir = File.dirname(__FILE__)

# Set this to the root of your project when deployed:
http_path = "/"

sass_path = dir
css_path = File.join(dir, "..", "css")
fonts_path = File.join(dir, "fonts")
images_path = File.join(dir, "..", "images")

javascripts_path = File.join(dir, "..", "javascripts")


output_style = :compressed
environment = :production

# output_style = :expanded
# environment = :development

# enable sourcemaps
# sourcemap = true

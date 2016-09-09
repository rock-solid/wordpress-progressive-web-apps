<?php

if (is_numeric(get_the_ID())){
    header("Location: ".home_url()."/#article/".get_the_ID());
} else {
    header("Location: ".home_url());
}
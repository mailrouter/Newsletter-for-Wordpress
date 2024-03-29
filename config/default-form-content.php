<?php

$email_label        = __('Email address', 'newsletter-for-wp');
$email_placeholder  = __('Your email address', 'newsletter-for-wp');
$signup_button      = __('Sign up', 'newsletter-for-wp');

$content = "<p>\n\t<label>{$email_label}: \n";
$content .= "\t\t<input type=\"email\" name=\"EMAIL\" placeholder=\"{$email_placeholder}\" required />\n</label>\n</p>\n\n";
$content .= "<p>\n\t<input type=\"submit\" value=\"{$signup_button}\" />\n</p>";

return $content;

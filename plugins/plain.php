<?php

fof_add_render_filter('fof_plain');

function fof_plain($text) {
	return strip_tags($text, "<a><b><i><blockquote><em><strong><p><pre><div><span>");
}
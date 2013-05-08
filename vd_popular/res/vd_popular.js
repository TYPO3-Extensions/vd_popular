/**
 * ajax counter
 * @param int page id
 */
function vd_popular_count(page) {
	var xhr = new XMLHttpRequest();
	xhr.open("GET", "?eID=vd_popular&vdpopularpageid=" + page, true);
	xhr.send(null);
	/* ignore result */
}


let $blk = $(".exitblock");
$(document).mouseleave(function (e) {
	if (e.clientY < 10) {
		if($blk[0]&&$blk.is(":hidden"))
		{
			$(".exitblock").fadeIn("fast");
		}
	}
});
$(document).click(function (e) {
	if (($blk.is(':visible')) && (!$(e.target).closest(".exitblock .modaltext").length)) {
		$blk.remove();
	}
});
function myFunction(e) {
	$blk.remove();
}
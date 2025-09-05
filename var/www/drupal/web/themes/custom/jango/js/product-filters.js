document.addEventListener("DOMContentLoaded", (event) => {
  var heading = document.createElement("h2");

	heading.innerHTML ="Filters and Sort";
	//document.getElementById("views-exposed-form-ajax-filterable-products-block-1").prepend(heading);
	var close = document.createElement("a");
	close.setAttribute("class", "close");
	close.setAttribute("onClick","open_filters();return false;");
	
	var close_img = document.createElement("img");
	close_img.setAttribute("src","/sites/default/files/images/filter/close.png");

	close.appendChild(close_img);
	//document.getElementById("views-exposed-form-ajax-filterable-products-block-1").prepend(close);

	var title_close = document.createElement("div");
	title_close.setAttribute("class","title_close");
	title_close.setAttribute("id","title_close_check");
	title_close.prepend(heading);
	title_close.prepend(close);
	block = document.querySelector("[id^=views-exposed-form-ajax-filterable-products-block]");
	if(block){
		block.prepend(title_close);
	}
});


function open_filters(){
	/* console.log("tr"); */
	var boolean = document.getElementById("title_close_check");
	/* console.log(boolean); */
	if(boolean == null){
		var heading = document.createElement("h2");

		heading.innerHTML ="Filters and Sort";
		//document.getElementById("views-exposed-form-ajax-filterable-products-block-1").prepend(heading);
		var close = document.createElement("a");
		close.setAttribute("class", "close");
		close.setAttribute("onClick","open_filters();return false;");
		
		var close_img = document.createElement("img");
		close_img.setAttribute("src","/sites/default/files/images/filter/close.png");

		close.appendChild(close_img);
		//document.getElementById("views-exposed-form-ajax-filterable-products-block-1").prepend(close);

		var title_close = document.createElement("div");
		title_close.setAttribute("class","title_close");
		title_close.setAttribute("id","title_close_check");
		title_close.prepend(heading);
		title_close.prepend(close);
		var form_div = document.querySelector("[id^=views-exposed-form-ajax-filterable-products-block]");
		if(form_div) form_div.prepend(title_close);
	}
	var form_div = document.querySelector("[id^=views-exposed-form-ajax-filterable-products-block]");
	if(!form_div.classList.contains("slideout")){
		/* form_div.style.display = "block"; */
		form_div.classList.add("slideout");
		form_div.classList.remove("slidein");
		document.getElementById("overlay").style.display = "block";
	}
	else{
		form_div.classList.add("slidein");
		/* form_div.style.display = "none"; */
		form_div.classList.remove("slideout");
		document.getElementById("overlay").style.display = "none";
	}
	

}
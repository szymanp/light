
Light.TabControl = {
	click: function(elem,tabname) {
		rootDiv = Light.UI.getParentNode(elem,"DIV").parentNode;
		oldDiv = $(rootDiv).children(".active").removeClass("active");
		newDiv = $(rootDiv).children().slice(tabname,tabname+1).addClass("active");
		$(newDiv).children("div").html("<p>Loading...</p>");

		env = Light.Env.CE[rootDiv.getAttribute("name")];
		
		jQuery.post(env.href,
				{ data: tabname },
				function(data,status,jqXHR)
					{
						$(newDiv).children("div").html(data);
					},
				"html");
	}
};

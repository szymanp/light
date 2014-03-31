Light.Table = {
	click: function(elem,key,colidx) {
		table = Light.UI.getParentNode(elem,"TABLE");
		form = Light.UI.getParentNode(table,"FORM");
		name = table.getAttribute("name");
		hidden = document.createElement("INPUT");
		hidden.setAttribute("name",name + "[_][cellClick]");
		hidden.setAttribute("type","hidden");
		hidden.setAttribute("value",colidx + "." + key);
		form.appendChild(hidden);
		form.submit();
	},
	
	columnClick: function(elem,col) {
		this.invokeAction(elem,"columnClick",col);
	},
	
	invokeAction: function(elem,name,arg) {
		table = Light.UI.getParentNode(elem,"TABLE");
		form = Light.UI.getParentNode(table,"FORM");
		tname = table.getAttribute("name");
		hidden = document.createElement("INPUT");
		hidden.setAttribute("name",tname + "[_]["+name+"]");
		hidden.setAttribute("type","hidden");
		hidden.setAttribute("value",arg);
		form.appendChild(hidden);
		form.submit();
	}	
};

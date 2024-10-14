document.addEventListener('DOMContentLoaded', () => {
	if (!window.oom.languages) {
		console.warning('[OOM-LABELS#1] Global languages list not found..');
	}

	// Click event for button "Add row" under labels list
	const btnListAddRow = document.getElementById("oomlabels-list-add_row");
	if (!btnListAddRow) {
		console.warning('[OOM-LABELS#2] Element ID "oomlabels-list-add_row" not found.');
	}
	if (btnListAddRow && window.oom.languages) {
		btnListAddRow.addEventListener("click", () => {
			const tblTestArr = document.getElementById("oomlabels-table-list");
			if (!tblTestArr) {
				return;
			}
			const trNewRow = tblTestArr.insertRow();
			if (!trNewRow) {
				return;
			}
			const tdLabel = trNewRow.insertCell();
			const inpLabel = document.createElement("input");
			inpLabel.name = "oomlabels-list["+trNewRow.rowIndex+"][label]";
			inpLabel.id = "oomlabels-list_"+trNewRow.rowIndex+"_label";
			inpLabel.type = "text";
			inpLabel.value = "";
			tdLabel.appendChild(inpLabel);

			for (const langObj of window.oom.languages) {
				const tdLang = trNewRow.insertCell();
				const inpLang = document.createElement("input");
				inpLang.name = "oomlabels-list["+trNewRow.rowIndex+"]["+langObj.code+"]";
				inpLang.id = "oomlabels-list_"+trNewRow.rowIndex+"_"+langObj.code;
				inpLang.type = "text";
				inpLang.value = "";
				tdLang.appendChild(inpLang);
			}
		});
	}

	// Click event for button "Add row" under languages list
	const btnLangAddRow = document.getElementById("oomlabels-languages-add_row");
	if (!btnLangAddRow) {
		console.warning('[OOM-LABELS#2] Element ID "oomlabels-list-add_row" not found.');
	}
	if (btnLangAddRow) {
		btnLangAddRow.addEventListener("click", () => {
			const tblTestArr = document.getElementById("oomlabels-table-languages");
			if (!tblTestArr) {
				return;
			}
			const trNewRow = tblTestArr.insertRow();
			if (!trNewRow) {
				return;
			}
			const tdLabel = trNewRow.insertCell();
			const inpLabel = document.createElement("input");
			inpLabel.name = "oomlabels-languages["+trNewRow.rowIndex+"]";
			inpLabel.id = "oomlabels-languages"+trNewRow.rowIndex+"_code";
			inpLabel.type = "text";
			inpLabel.value = "";
			tdLabel.appendChild(inpLabel);

			const tdLang = trNewRow.insertCell();
		});
	}
});
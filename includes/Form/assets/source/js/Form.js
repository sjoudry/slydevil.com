window.addEventListener('DOMContentLoaded', () => {
	class SDF {
		constructor() {
			this.FormSettings = null;

			this.getFormSettings = () => {
				const jsonElement = document.querySelector('[data-form-settings]');
				if (jsonElement && jsonElement.innerHTML) {
					return JSON.parse(jsonElement.innerHTML);
				}

				return null;
			};

			this.handleFieldset = (fieldset) => {
				const collapsed = document.getElementById('FIELDSET-' + fieldset.id + '-COLLAPSED');
				const fields = fieldset.getElementsByTagName('div');
				Array.from(fields).forEach((field) => {
					if (collapsed.value === '0') {
						field.style.display = 'none';
						fieldset.classList.add('fieldset_collapsed');
						fieldset.classList.remove('fieldset_expanded');
					}
					else {
						if (field.dataset.visible === undefined || field.dataset.visible === '1') {
							field.style.display = 'block';
							fieldset.classList.add('fieldset_expanded');
							fieldset.classList.remove('fieldset_expanded');
						}
					}
				});
				collapsed.value = collapsed.value === '1' ? '0' : '1';
			};

			this.hideInlineError = (fieldId, validator, group) => {
				this.toggleInlineError(fieldId, validator, group, false);
				return true;
			};

			this.init = () => {
				this.FormSettings = this.getFormSettings();

				// Handle fieldset collapsing.
				const fieldsets = document.querySelectorAll('fieldset[data-collapsible="1"]');
				Array.from(fieldsets).forEach((fieldset) => {
					const collapsed = document.getElementById('FIELDSET-' + fieldset.id + '-COLLAPSED');
					if (collapsed.value === '1') {
						this.handleFieldset(fieldset);
					}
					const toggle = fieldset.querySelector('legend > a');
					if (toggle) {
						toggle.addEventListener('click', (e) => {
							this.handleFieldset(fieldset);
							e.preventDefault();
						});
					}
				});

				// Handle validators.
				if (this.FormSettings) {
					Object.entries(this.FormSettings).forEach(([form, settings]) => {
						const formObject = document.getElementById(form);
						Object.entries(settings).forEach(([type, fields]) => {
							Object.entries(fields).forEach(([id, config]) => {
								const field = document.getElementById(id);
								if (type === 'existance') {
									field.addEventListener('change', (e) => { this.validateExistance(id, e, true); });
									formObject.addEventListener('submit', (e) => { this.validateExistance(id, e, true); });
								}
								else if (type === 'file_type') {
									field.addEventListener('change', (e) => { this.validateFileTypes(id, e, config); });
									formObject.addEventListener('submit', (e) => { this.validateFileTypes(id, e, config); });
								}
								else if (type === 'length_long') {
									field.addEventListener('change', (e) => { this.validateLengthLong(id, e, config); });
									formObject.addEventListener('submit', (e) => { this.validateLengthLong(id, e, config); });
								}
								else if (type === 'length_short') {
									field.addEventListener('change', (e) => { this.validateLengthShort(id, e, config); });
									formObject.addEventListener('submit', (e) => { this.validateLengthShort(id, e, config); });
								}
								else if (type === 'match') {
									Object.entries(config).forEach(([other, match], index) => {
										field.addEventListener('change', (e) => { this.validateMatch(id, other, e, index, match); });
										formObject.addEventListener('submit', (e) => { this.validateMatch(id, other, e, index, match); });
									});
								}
								else if (type === 'maximum_checked') {
									const parent = field.closest('.form-element-group');
									const inputs = parent.querySelectorAll('input:not([type="hidden"])');
									Array.from(inputs).forEach((input) => {
										input.addEventListener('change', (e) => { this.validateMaximumChecked(id, e, config); });
									});
									formObject.addEventListener('submit', (e) => { this.validateMaximumChecked(id, e, config); });
								}
								else if (type === 'maximum_selected') {
									field.addEventListener('change', (e) => { this.validateMaximumSelected(id, e, config); });
									formObject.addEventListener('submit', (e) => { this.validateMaximumSelected(id, e, config); });
								}
								else if (type === 'minimum_checked') {
									const parent = field.closest('.form-element-group');
									const inputs = parent.querySelectorAll('input:not([type="hidden"])');
									Array.from(inputs).forEach((input) => {
										input.addEventListener('change', (e) => { this.validateMinimumChecked(id, e, config); });
									});
									formObject.addEventListener('submit', (e) => { this.validateMinimumChecked(id, e, config); });
								}
								else if (type === 'minimum_selected') {
									field.addEventListener('change', (e) => { this.validateMinimumSelected(id, e, config); });
									formObject.addEventListener('submit', (e) => { this.validateMinimumSelected(id, e, config); });
								}
								else if (type === 'pattern') {
									Object.entries(config).forEach(([other, match], index) => {
										field.addEventListener('change', (e) => { this.validatePattern(id, e, index, new RegExp(other), match); });
										formObject.addEventListener('submit', (e) => { this.validatePattern(id, e, index, new RegExp(other), match); });
									});
								}
							});
						});
					});
				}
			};

			this.showInlineError = (fieldId, validator, group) => {
				this.toggleInlineError(fieldId, validator, group, true);
				return false;
			};

			this.toggleInlineError = (fieldId, validator, group, show) => {
				const errorGroup = document.getElementById('form-error-group-' + fieldId);
				const individualError = document.getElementById('form-individual-error-' + validator + '-' + group + '-' + fieldId);
				if (show) {
					individualError.dataset.visible = '1';
					individualError.style.display = 'block';
					errorGroup.dataset.visible = '1';
					errorGroup.style.display = 'block';
				}
				else {
					individualError.dataset.visible = '0';
					individualError.style.display = 'none';
					let errors = false;
					Array.from(errorGroup.children).forEach((field) => {
						if (field.dataset.visible === '1') {
							errors = true;
						}
					});
					if (!errors) {
						errorGroup.dataset.visible = '0';
						errorGroup.style.display = 'none';
					}
				}
			};

			this.validateExistance = (fieldId, event, showError) => {
				const field = document.getElementById(fieldId);
				if ((field.value === null || field.value === '')) {
					if (showError) {
						this.showInlineError(fieldId, 'existance', '0');
					}
					event.preventDefault();
					return false;
				}
				if (showError) {
					this.hideInlineError(fieldId, 'existance', '0');
				}
				return true;
			};

			this.validateFileTypes = (fieldId, event, fileTypes) => {
				let error = false;

				if (this.validateExistance(fieldId, event, false)) {
					const parts = document.getElementById(fieldId).value.split('.');
					const type = parts[parts.length - 1].toLowerCase();
					error = !fileTypes.includes(type);
				}
					
				if (error) {
					event.preventDefault();
					return this.showInlineError(fieldId, 'file_type', '0');
				}
				return this.hideInlineError(fieldId, 'file_type', '0');
			};

			this.validateLengthLong = (fieldId, event, fieldLength) => {
				let error = false;

				if (this.validateExistance(fieldId, event, false)) {
					error = (document.getElementById(fieldId).value.length > fieldLength);
				}

				if (error) {
					event.preventDefault();
					return this.showInlineError(fieldId, 'length_long', '0');
				}
				return this.hideInlineError(fieldId, 'length_long', '0');
			};

			this.validateLengthShort = (fieldId, event, fieldLength) => {
				let error = false;

				if (this.validateExistance(fieldId, event, false)) {
					error = (document.getElementById(fieldId).value.length < fieldLength);
				}

				if (error) {
					event.preventDefault();
					return this.showInlineError(fieldId, 'length_short', '0');
				}
				else {
					return this.hideInlineError(fieldId, 'length_short', '0');
				}
			};

			this.validateMatch = (fieldId, matchFieldId, event, group, match) => {
				let error = false;

				if (this.validateExistance(fieldId, event, false)) {
					if (match) {
						error = (document.getElementById(fieldId).value != document.getElementById(matchFieldId).value);
					}
					else {
						error = (document.getElementById(fieldId).value == document.getElementById(matchFieldId).value);
					}
				}

				if (error) {
					event.preventDefault();
					return this.showInlineError(fieldId, 'match', group);
				}
				return this.hideInlineError(fieldId, 'match', group);
			};

			this.validateMaximumChecked = (fieldId, event, maximumChecked) => {
				const checkboxes = document.getElementById('form-element-' + fieldId).querySelectorAll('input:not([type="hidden"])');

				let checked = 0;

				for (let i = 0; i < checkboxes.length; i++) {
					if (checkboxes[i].checked) {
						checked++;
					}
				}

				if (checked > maximumChecked) {
					event.preventDefault();
					return this.showInlineError(fieldId, 'maximum_checked', '0');
				}
				return this.hideInlineError(fieldId, 'maximum_checked', '0');
			};

			this.validateMaximumSelected = (fieldId, event, maximumSelected) => {
				const select = document.getElementById(fieldId);
				let selected = 0;

				for (let i = 0; i < select.length; i++) {
					if (select[i].selected) {
						selected++;
					}
				}

				if (selected > maximumSelected) {
					event.preventDefault();
					return this.showInlineError(fieldId, 'maximum_selected', '0');
				}
				return this.hideInlineError(fieldId, 'maximum_selected', '0');
			};

			this.validateMinimumChecked = (fieldId, event, minimumChecked) => {
				const checkboxes = document.getElementById('form-element-' + fieldId).querySelectorAll('input:not([type="hidden"])');
				let checked = 0;

				for (let i = 0; i < checkboxes.length; i++) {
					if (checkboxes[i].checked) {
						checked++;
					}
				}

				if (checked < minimumChecked) {
					event.preventDefault();
					return this.showInlineError(fieldId, 'minimum_checked', '0');
				}
				return this.hideInlineError(fieldId, 'minimum_checked', '0');
			};

			this.validateMinimumSelected = (fieldId, event, minimumSelected) => {
				const select = document.getElementById(fieldId);
				let selected = 0;

				for (let i = 0; i < select.length; i++) {
					if (select[i].selected) {
						selected++;
					}
				}

				if (selected < minimumSelected) {
					event.preventDefault();
					return this.showInlineError(fieldId, 'minimum_selected', '0');
				}
				return this.hideInlineError(fieldId, 'minimum_selected', '0');
			};

			this.validatePattern = (fieldId, event, group, pattern, match) => {
				let error = false;

				if (this.validateExistance(fieldId, event, false)) {
					const field = document.getElementById(fieldId);
					if (match) {
						error = (!pattern.test(field.value));
					}
					else {
						error = (pattern.test(field.value));
					}
				}

				if (error) {
					event.preventDefault();
					return this.showInlineError(fieldId, 'pattern', group);
				}
				return this.hideInlineError(fieldId, 'pattern', group);
			};
		}
	};

	const forms = new SDF();
	forms.init();
});

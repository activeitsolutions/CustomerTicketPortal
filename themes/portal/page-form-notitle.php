</head>
<body>

	<!-- Primary Page Layout
	–––––––––––––––––––––––––––––––––––––––––––––––––– -->
	<div class="contentcontainer">
		<div class="content">
			<div class="section group">
				<form id="ticketForm">
					<div class="form-group">
						<label for="Ticket Title"><strong>Ticket Title</strong> <span class="required-asterisk">*</span> <span class="tooltip-icon" data-tooltip="Please provide a clear and descriptive title to help us quickly identify your issue from a list and make it easier to locate your ticket through search in the future." style="cursor: pointer;"><i class="fa fa-info-circle" aria-hidden="true"></i></span></label>
						<input type="text" id="Ticket Title" name="Ticket Title" placeholder="<?php echo $pagetitle; ?>" required="required">
					</div>
					<?php
						$filename = file_get_contents("./pages/" . $pagename . ".html");
						// Parse and replace shortcodes
						$parsed_content = parse_shortcodes($filename);
						echo $parsed_content;
					?>
					<button type="button" id="sendEmailButton">Send Email</button>
				</form>
				<script>
					document.getElementById('sendEmailButton').addEventListener('click', function() {
						let allValid = true; // Flag to check if all required fields are valid
						let errorMessage = 'Please fill out the following required fields:\n';
						let emailBody = ''; // Initialize the email body text
						// Validation Function
						const validateForm = () => {
							const formElements = document.getElementById('ticketForm').elements;
							let isValid = true;
							let checkedRadioGroups = {}; // To track radio button groups
							for (let i = 0; i < formElements.length; i++) {
								const element = formElements[i];
								// Check if the element is required and empty/invalid
								if (element.hasAttribute('required')) {
									// For radio buttons: Check if at least one in the group is selected
									if (element.type === 'radio') {
										const groupName = element.name;
										if (!checkedRadioGroups[groupName]) {
											const radioGroup = document.querySelectorAll(`input[name="${groupName}"]:checked`);
											if (radioGroup.length === 0) {
												isValid = false;
												const label = element.closest('.form-group').querySelector('label');
												const labelText = label ? label.childNodes[0].textContent.trim() : groupName;
												errorMessage += `• ${labelText}\n`;
												checkedRadioGroups[groupName] = true; // Mark the group as validated
											}
										}
									}
									// For checkboxes: Ensure at least one checkbox in the group is selected
									else if (element.type === 'checkbox') {
										const checkboxGroup = document.querySelectorAll(`input[name="${element.name}"]:checked`);
										if (checkboxGroup.length === 0) {
											isValid = false;
											const label = element.closest('.form-group').querySelector('label');
											const labelText = label ? label.childNodes[0].textContent.trim() : element.name;
											errorMessage += `• ${labelText}\n`;
										}
									}
									// For other inputs (text, textarea, select): Check if they are empty
									else if (!element.value.trim()) {
										isValid = false;
										const label = document.querySelector(`label[for="${element.id}"]`);
										if (label) {
											const labelText = label.childNodes[0].textContent.trim();
											errorMessage += `• ${labelText}\n`;
										}
									}
								}
							}
							return isValid;
						};
						// Email Body Construction Function
						const constructEmailBody = () => {
							const formElements = document.getElementById('ticketForm').elements;
							let currentGroupLabel = '';
							for (let i = 0; i < formElements.length; i++) {
								const element = formElements[i];
								// Process form elements (input, textarea, select)
								if (element.tagName === 'INPUT' || element.tagName === 'TEXTAREA' || element.tagName === 'SELECT') {
									// Handle group labels (for radio buttons or checkboxes)
									if (element.type === 'radio' || element.type === 'checkbox') {
										const parentLabel = element.closest('.form-group').querySelector('label').childNodes[0].textContent.trim();
										if (parentLabel !== currentGroupLabel) {
											currentGroupLabel = parentLabel;
											emailBody += `
					<strong>${currentGroupLabel}:</strong>\n`;
										}
										if (element.checked) {
											emailBody += element.nextElementSibling.textContent.trim() + '\n';
										}
									} else {
										// For text, textarea, email, select fields
										const label = document.querySelector(`label[for="${element.id}"]`);
										if (label) {
											const labelText = label.childNodes[0].textContent.trim();
											emailBody += `
					<strong>${labelText}:</strong>\n` + element.value.trim() + '\n';
										}
									}
								}
							}
						};
						// First validate the form
						allValid = validateForm();
						// If all fields are valid, construct the email body and send it
						if (allValid) {
							constructEmailBody();
							const ticketTitle = document.getElementById('Ticket Title').value;
							const mailtoLink = `mailto:bwoods@domain.com?subject=${encodeURIComponent(ticketTitle)}&body=${encodeURIComponent(emailBody.trim())}`;
							window.location.href = mailtoLink;
						} else {
							// If validation fails, alert the user with the error message
							alert(errorMessage);
						}
					});
				</script>
			</div>
		</div>
	</div>

<!-- End Document
  –––––––––––––––––––––––––––––––––––––––––––––––––– -->
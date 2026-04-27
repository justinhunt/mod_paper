# Paper Module (mod_paper)

Paper is a Moodle activity module designed to bridge the gap between traditional paper-based student work and digital grading. It allows teachers to create custom response templates, scan student handwriting, and use AI-powered OCR and grammar correction to streamline the evaluation process.

## Features

- **Custom Template Designer**: Define specific response areas on a PDF template.
- **AI-Powered OCR**: Automatically extracts student handwriting from scanned documents.
- **Grammar Correction**: Integrates with LLMs to provide automated feedback and "arrows-to" correction overlays.
- **Manual Grading Sidebar**: Easily review student work, adjust grades, and provide specific feedback.
- **"Display Only" Snippets**: Capture and display original handwriting/marks (e.g., for multiple choice or drawing questions) without OCR.
- **PDF Evaluation Reports**: Generate high-quality PDF reports for students showing their original work with digital overlays.
- **Gradebook Integration**: Automatically syncs total scores back to the Moodle gradebook.

## Workflow

1. **Setup**: Define your response areas on the template (Full Name, Username, Standard, or Display Only).
2. **Print**: Download the generated PDF template and distribute it to students.
3. **Scan**: Collect and scan the completed student papers as a single PDF.
4. **Process**: Upload the scanned PDF to the activity. The system will unroll the pages and perform OCR.
5. **Evaluate**: Review the results in the Evaluation Report. Use the interactive sidebar to verify AI corrections and assign manual grades.
6. **Report**: Share the digital evaluations with students or download the combined PDF report.

## Technical Requirements

- **Ghostscript**: Required for PDF to Image conversion during processing.
- **AI API**: Configured in the plugin settings for text extraction.
- **Grammar API**: Configured for automated correction logic.
- **File Storage**: Uses Moodle's File API to store template snippets and evaluation reports.

## Installation

1. Copy the `paper` directory to your Moodle's `/mod/` folder.
2. Log in as an administrator and go to `Site Administration > Notifications` to complete the installation.
3. Configure the necessary API keys in `Site Administration > Plugins > Activity modules > Paper`.

## Permissions

- `mod/paper:addinstance`: Allow creating new Paper activities.
- `mod/paper:submit`: Allow students to view their evaluations.
- `mod/paper:manage`: Allow teachers to setup templates, process scans, and grade work.

# mod_paper

## Outline 

A Moodle activity plugin that gives corrections, feedback and optionally a score on student’s written assignments or worksheets.

## Workflow

### Setup

1. Teacher opens new mod\_paper activity instance  
2. Teacher scans a single submission and uploads it as PDF or JPG.   
3. mod\_paper displays the scanned image and identifies the response areas the students can write in. The scanned image has red bounding box and a response-number overlaid over it.  
4. Mod\_paper prepares a form for each response area. The form clearly displayed the response-number.  
5. Teacher marks one response-area as the “name” field  
6. For other response areas teacher sets:  
   1. The question / topic (textbox)  
   2. The Correct answer (radio button \+ fields): none, exactly, is relevant to question, same meaning as \[textbox\]  
   3. Give Grammar corrections? (radio buttons): no, on major mistakes, on all mistakes  
   4. Give Feedback? (multi checkbox):  on grammar mistakes, on incorrect answers, overall   
   5. Give Grade? (radio button \+ fields): no ,  maximum grade \[textbox\] \+ instructions on how to calculate \[textbox\]

   

   NB 

* teacher may omit response areas and they will be ignored  
* Teacher may return to the setup area and adjust settings and re-run submission processing


### Submission processing

1. Teacher uploads a multipage PDF / JPG (multiple submissions) or multiple individual PDF / JPG submissions  
2. Mod\_paper identifies the response areas in each submission and determines the evaluation: i.e grammar corrections , feedback and grade.  
3. The evaluation is saved in a database table where it can later be fetched.  
4. Mod\_paper prepares a single A4 pdf for all submissions, and an evaluation page in that PDF for each submission. The evaluation page contains for each submitted response  
* Question:  
* OCR’d text:  
* Grammar Corrected Text (if configured)  incorrect struck out followed by corrected text in bold  
* Feedback (if configured): language of feedback from activity settings  
* Grade  (if configured)  
    
5. Each evaluation page has the students name OCR’d from name field at top right  
6. Teacher downloads (or chooses to print) the evaluation PDF

## Instance Configuration options

* Standard activity module options  
* Name field role:  Moodle username or free text  
* Target language: The language the student is writing in  
* Feedback language: The language feedback should be given in


## Admin Configuration Options

* Default target language  
* Default feedback language  
* Open AI credentials

## Reporting and Grading

A submissions report shows the summary list of the submission-evaluations

* Submission \- name   
* Submission \- total grade   
* View link  
* Delete link

Clicking on the view link takes you to an on screen rendering of the evaluation  
Clicking on delete , after a confirmation popup, will delete the evaluations

the total grade of the activity is calculated as; the total of the users response grades /  sum of the max grades for the response areas.

 If the activity instance name field role is “username”  and the username exists and the user is  enrolled in the course , the grade is saved in the gradebook.

## AI and PDF handling

 The activity should have an AI manager class  which abstract calls to AI services, and an OpenAI class which the manager submits AI service calls to. In future there may be other AI providers.

JPG to PDF conversion should be done using ghostscript, it is a dependency of this mod. PDF writing should be done using the same libraries the assignfeedback\_annotatepdf plugin uses.  

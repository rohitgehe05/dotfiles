This version is 2.07
============================================================

Theme’s instruction is in the folder ‘document/instruction’. Please open that folder and open the file ‘index.html’ with your browsers.

For using payment with Stripe, Authorized.net and PayMill. The system don’t collect any of user’s credit card information. Please note that we don’t take any responsibilities for any damages to credit card information caused by system’s vulnerability so please make sure that your hosting is secured and also your being used plugins are trustable.

============================================================

==v2.07== 05/10/2016
update french translation for first and last name
	- include/languages folder

fix minor bugs
fix deprecate function
fix author naming display
fix lock course date
continue the course where it left off
change variable naming to aviod confusion
display time on on-site course
add class to course item button
add class to course info section
add function to order the section lecture and quiz
use ajax to save the quiz timer / answer every 5 second to prevent timer reset
improve the retake quiz option 
	- author-default.php
	- lms-script.js
	- single-section-quiz.php
	- single-course-quiz.php
	- single-course-content.php
	- framework/table-management.php
	- framework/course-option.php
	- framework/javascript/meta-box.js
	- framework/javascript/quiz-question.js
	- framework/stylesheet/meta-box.css
	- include/course-item.php
	- include/utility.php
	- include/instructor.php
	- include/lightbox-form.php
	- include/attendance-section.php
	- include/gdlr-payment-query.php
	- user/leader-board.php
	- user/manual-check-needed.php
	- user/badge-certificate.php
	- user/my-course-student.php
	- user/scoring-status-student.php
	- user/profile.php

==v2.06== 17/08/2016
update Master Slider

update importer to show original image instead of the placeholder.
	- goodlayers-importer plugin
	
add instagram icon	
	- include/gdlr-social-icon.php
	- images folder
	
update font awesome
	- plugins/font-awesome folder
	- include/gdlr-include-script.php

==v2.05== 25/05/2016
- fix booking form scrolling in small display
	style.css

==v2.04== 25/03/2016
- fix shortcode spacing
	include/plugin/shortcode-generator.php
	
- add revision feature
	gdlr-revision.php
	functions.php

==v2.03== 20/12/2015
- wp 4.4 compatibility
	framework/javascirpt/gdlr-sidebar-generator.js
	include/function-blog-item.php
	
- update master slider

==v2.02== 09/12/2015
fix icon with new font awesome version
	gdlr-shortcode plugin
	include/function/gdlr-utility.php
	include/function/gdlr-page-item.php

==v2.01== 07/12/2015
- Fix rating (star disappeared)
	Good LMS plugin (lms-script.js)

==v2.00== 27/11/2015
- shortcode fix
	gdlr-shortcode plugin
	include/function/gdlr-utility.php
- wp 4.3 compat
	include/widget folder
	framework/function/gdlr-sidebar-generator.php
- update functionality
	framework/function/gdlr-page-option.php
	include/course-item.php
	framework/gdlr-theme-sync.php
**Good LMS Plugin**
- wp 4.3 compatibiltiy
- Onsite course widget date
- Add button to proceed quiz
- Sort Course By Start Date *** Have to save every course that's suppose to show once for this update to take effects
- Add new course info style
- Auto login after register
- Add option to lock the course until start date is reached
- Russian translation by : Andeo
	framework/javascript/meta-box.js
	framework/plugin-option/course-category-widget.php
	framework/plugin-option/popular-course-widget.php
	framework/plugin-option/recent-course-widget.php
	framework/course-option.php
	framework/gdlr-coupon-option.php
	framework/gdlr-course-content-bkup.php
	framework/gdlr-theme-sync.php
	framework/plugin-option.php
	framework/meta-template.php
	framework/user.php
	user/my-course.php
	user/my-course-student.php
	user/attended-course.php
	user/missing-course.php
	include/lightbox-form.php
	include/course-item.php
	include/paymill-payment.php
	include/payment-api/braintree-php
	include/cloud-paymen.php
	include/shortcode.php
	include/stripe-payment.php
	include/utility.php
	include/course-item.php
	author.php
	register.php
	single-braintree.php
	single-course-content.php
	single-course-quiz.php
	single-section-quiz.php
	single-authorize.php
	lms-style.css

==v1.27== 20/07/2015
- fix login incorrect and lost password page
- Spanish translation by 'Stewart Vallely'
- add payment status changing at transaction page

==v1.26== 20/06/2015
- display message to login before taking the quiz
- update font awesome version (in lms plugin)
	user/earning.php
	user/badge-certificate.php
	include/misc.php
	include/lightbox-form.php
	include/course-item.php
	include/certificate-item.php
	framework/plugin-option/statement.php
	single-course.php
	single-course-quiz.php
	single-course-content.php
	author-default.php

==v1.25== 10/06/2015
- Payment fixed
	Goodlms plugin.(single-authorize.php,include/lightbox-form.php)

==v1.24== 12/05/2015
- Update Master Slider

==v1.23== 08/05/2015

- wp 4.1 customizer
	include/gdlr-admin-option.php
	
- fix twitter with special character
	plugins/twitter-oauth.php
	include/function/gdlr-media.php
	include/widget/twitter-widget.php
	
- xss
	- include/plugin/class-tgm-plugin-activation.php

==v1.22== 05/03/2015
These are logs for LMS plugin  
  - fix course grid space
  - fix insturctor author image in safari
	lms-style.css
  - wpml compatibility
  - fix non-latin character for form booking
  	include/login-form.php
  	login.php
	include/lightbox-form.php
  - php5.3 compatibility issues 
	goodlayers-lms.php

==v1.21== 04/02/2015
- add transation to user booked course area 
 user/booked-course.php
- display error for non-latin character as username
 register.php 
 include/lightbox-form.php
- fix course shortcode displaying problem
 include/shortcode.php

==v1.20== 15/01/2015
- add option for jQuery ui inclusion
- unlock sections/course by specific date
- units in progress bar can be clicked for convenience
- free course without logging in
- prerequisite for courses
- Instructor can see student list for each courses
- put rating in single course
- removable questions for quiz
- put confirmation(unique code) code shortcode in certificate(for verification purpose)
- instructor can remove student from course
- leadership board for quiz
- put more payment gateways: Stripe, PayMill, Authorised.net 
  *for all these updates, update goodlayers lms plugin

==v1.12== 09/12/2014
- fix empty max-seat for offline course
- improve the paypal validation
 	lms-script.js
	 include/lightbox-form
	 user/book-course.php
- goodlayers lms plugin

==v1.11== 15/10/2014
 goodlayers lms plugin

==v1.10== 27/09/2014
- fix sub sub menu flickering
	plugins/superfish/css/superfish.css
	
- fix header in course category page
	header-title.php
	tribe-events/default-template.php
	
- re-takable quiz 
- show answers
- wipe student data when remove student account
- disable payment option
- ability to disable manual transfer method
- student cancel booked course
- pdf icon for courses
- Free onsite course
- instrutor’s commission rate
- put time period option for student to continue to next course section
- Course rating
- Certificate
- Badge
- Transaction pagination
- Instructor can check earning in backend
 goodlayers lms plugin

==v1.01== 02/09/2014
- fix instructor capability to delete / read / edit courses 
- fix responsive issues 
 goodlayers lms plugin
 
- fix header in course category page
 header-title.php

==v1.00== 25/08/2014
* initial released 
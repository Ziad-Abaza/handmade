<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class StaticContentSeeder extends Seeder
{
    public function run()
    {
        // Seed Static Pages
        DB::table('static_pages')->insert([
            [
                'title' => 'Privacy Policy',
                'slug' => 'privacy-policy',
                'content' => <<<EOT
# Privacy Policy – Handmade Marketplace

**Last Updated: December 21, 2024**

## Introduction
Handmade Marketplace respects your privacy and is committed to protecting your personal data. This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you access or use our services.

## Information We Collect

### Personal Information
- Full name, email address, phone number
- Account credentials (securely encrypted)
- Billing and payment details
- Academic records and certificates

### Usage Information
- Login history and session data
- Pages viewed and interactions
- Device type, browser, IP address

### Communication Data
- Support requests
- Messages and feedback
- Instructor and student communications

## How We Use Your Data
- Provide and manage platform services
- Improve user experience and performance
- Process payments and subscriptions
- Ensure platform security and compliance

## Data Sharing
We may share your data with:
- Payment processors
- Hosting and cloud providers
- Analytics and monitoring services

All third parties are required to comply with strict data protection obligations.

## Data Security
We implement encryption, access control, and regular security audits to protect your information from unauthorized access or misuse.

## Your Rights
You have the right to access, update, correct, or delete your personal data, subject to applicable laws.

## Data Retention
Data is retained only for as long as necessary to provide services or meet legal obligations.

## Contact
Email: privacy@egtech.edu  
Phone: +20 123 456 7895

## Effective Date
This policy is effective as of December 21, 2024.
EOT,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'title' => 'Terms & Conditions',
                'slug' => 'terms-conditions',
                'content' => <<<EOT
# Terms and Conditions – Handmade Marketplace

**Last Updated: December 21, 2024**

## Acceptance of Terms
By accessing or using the Handmade Marketplace, you agree to be bound by these Terms and Conditions.

## User Accounts
- Users must provide accurate registration information
- You are responsible for maintaining account security
- Unauthorized use must be reported immediately

## Platform Usage
You agree not to:
- Use the platform for unlawful activities
- Attempt to access restricted areas
- Disrupt or compromise system security

## Payments and Subscriptions
- Fees are non-refundable unless stated otherwise
- Subscription terms are clearly defined at purchase
- Payment processing is handled by third-party providers

## Intellectual Property
All content, courses, logos, and materials are owned by Handmade Marketplace or its licensors and may not be reproduced without permission.

## Termination
We reserve the right to suspend or terminate accounts that violate these terms without prior notice.

## Limitation of Liability
Handmade Marketplace is not liable for indirect, incidental, or consequential damages arising from platform use.

## Governing Law
These terms are governed by and interpreted under the laws of Egypt.

## Changes to Terms
We may update these terms at any time. Continued use of the platform constitutes acceptance of the updated terms.

## Contact Information
Email: support@egtech.edu  
Phone: +20 123 456 7890

## Effective Date
These terms are effective as of December 21, 2024.
EOT,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);


        // Seed FAQs
        DB::table('faqs')->insert([
            [
                'question' => 'How do I register for courses?',
                'answer' => 'You can register for courses by logging into your account, navigating to the courses section, and selecting the courses you wish to enroll in. Follow the payment process to complete your registration. You\'ll receive a confirmation email once enrollment is complete.',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'question' => 'What payment methods are accepted?',
                'answer' => 'We accept various payment methods including Visa, Mastercard, American Express, PayPal, and local Egyptian payment methods like Fawry and ValU. All transactions are secured with industry-standard 256-bit SSL encryption.',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'question' => 'Can I get a refund if I\'m not satisfied?',
                'answer' => 'Yes, we offer a 30-day money-back guarantee for most courses. If you\'re not satisfied with your purchase, contact our support team within 30 days of purchase. Refunds are typically processed within 5-7 business days.',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'question' => 'How long do I have access to courses?',
                'answer' => 'Once enrolled, you have lifetime access to course materials, including all future updates. You can learn at your own pace and revisit the content anytime. Some specialized programs may have time-limited access - check individual course details.',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'question' => 'Are certificates provided upon completion?',
                'answer' => 'Yes, upon successful completion of a course with at least 80% score on final assessments, you will receive a verifiable certificate of completion. Certificates include QR codes for verification and can be added to your resume and LinkedIn profile.',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'question' => 'What are the system requirements?',
                'answer' => 'You need a modern web browser (Chrome 90+, Firefox 88+, Safari 14+, or Edge 90+), stable internet connection (minimum 5 Mbps), and at least 2GB RAM. For coding courses, you\'ll need a text editor (VS Code recommended) and relevant software development tools.',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'question' => 'Can I download course materials?',
                'answer' => 'Yes, most course materials including videos (720p), transcripts, PDF resources, and project files can be downloaded for offline viewing. Some interactive content like live sessions and quizzes require internet access.',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'question' => 'Is there mobile app support?',
                'answer' => 'Yes, we have mobile apps for both iOS (iOS 13+) and Android (Android 8+) devices. You can download them from the App Store or Google Play Store. The apps support offline viewing, progress sync, and push notifications.',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'question' => 'How do I contact instructors?',
                'answer' => 'You can contact instructors through course discussion forums, direct messaging within the platform, or during scheduled live office hours (typically weekly). Response times are usually within 24-48 hours for forums and immediate during office hours.',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'question' => 'Are there group discounts available?',
                'answer' => 'Yes, we offer discounts for corporate training, educational institutions, and groups: 5-9 students (15% off), 10-24 students (25% off), 25+ students (35% off). Contact our sales team at sales@egtech.edu for custom pricing and invoicing.',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'question' => 'What if I need technical support?',
                'answer' => 'Our technical support team is available 24/7 via email (support@egtech.edu), live chat, and phone (+20 123 456 7892). We also have an extensive knowledge base, video tutorials, and community forums for self-help. Priority support is available for premium members.',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'question' => 'Can I transfer my course access?',
                'answer' => 'Course access is non-transferable and tied to your individual account. However, you can gift courses to others by purchasing them directly through our platform. Gift recipients receive a special enrollment code.',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'question' => 'Do you offer financial aid or scholarships?',
                'answer' => 'Yes, we offer need-based scholarships and merit-based financial aid for eligible students. Applications are reviewed quarterly. We also partner with various organizations to provide sponsored learning opportunities. Visit our financial aid page for more information.',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'question' => 'How are courses structured and delivered?',
                'answer' => 'Courses are structured into modules with video lessons, reading materials, practical assignments, quizzes, and final projects. Most courses are self-paced, but some include scheduled live sessions. Average course duration is 4-12 weeks with 3-6 hours of study per week recommended.',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'question' => 'Can I get academic credit for courses?',
                'answer' => 'While our courses are primarily professional development focused, we have partnerships with several universities that may grant academic credit for completed courses. Contact your institution directly to confirm credit transfer policies.',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'question' => 'What is your cancellation policy?',
                'answer' => 'You can cancel your enrollment within 30 days for a full refund. After 30 days, you may cancel but no refund will be issued. Subscription plans can be cancelled anytime, with access continuing until the end of the billing period.',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'question' => 'Do you offer corporate training solutions?',
                'answer' => 'Yes, we provide customized corporate training programs for businesses of all sizes. Our solutions include tailored curriculum, dedicated support, progress tracking, and certification. Contact our enterprise team at enterprise@egtech.edu for a consultation.',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'question' => 'How do I report technical issues or bugs?',
                'answer' => 'Report technical issues through the support ticket system or email bugs@egtech.edu. Include details like your browser/device, error messages, and steps to reproduce the issue. Critical bugs are typically resolved within 24 hours.',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);

        // Seed Support Tickets (assuming we have at least one user)
        // First, let's get a user ID or create a sample user
        $userId = DB::table('users')->first()?->id;

        if (!$userId) {
            // Create a sample user if none exists
            $userId = DB::table('users')->insertGetId([
                'name' => 'John Doe',
                'email' => 'john.doe@example.com',
                'password' => Hash::make('password'),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }

        DB::table('support_tickets')->insert([
            [
                'user_id' => $userId,
                'subject' => 'Login Issue',
                'message' => 'I am unable to log into my account. I have tried resetting my password but the reset email is not arriving.',
                'status' => 'open',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'user_id' => $userId,
                'subject' => 'Course Access Problem',
                'message' => 'I purchased the Web Development course yesterday but I cannot access the course materials. The payment was successful but the course is not showing in my dashboard.',
                'status' => 'pending',
                'created_at' => Carbon::now()->subDay(),
                'updated_at' => Carbon::now()->subDay(),
            ],
            [
                'user_id' => $userId,
                'subject' => 'Certificate Request',
                'message' => 'I completed the Database Management course last week but haven\'t received my certificate yet. Can you please help me with this?',
                'status' => 'closed',
                'created_at' => Carbon::now()->subDays(3),
                'updated_at' => Carbon::now()->subDays(2),
            ],
            [
                'user_id' => $userId,
                'subject' => 'Technical Support',
                'message' => 'The video player is not working properly on my mobile device. Videos keep buffering and the quality is poor even with good internet connection.',
                'status' => 'open',
                'created_at' => Carbon::now()->subHours(6),
                'updated_at' => Carbon::now()->subHours(6),
            ],
            [
                'user_id' => $userId,
                'subject' => 'Payment Issue',
                'message' => 'My credit card was charged twice for the same course. I need a refund for one of the charges. Transaction ID: #12345 and #12346.',
                'status' => 'pending',
                'created_at' => Carbon::now()->subDays(2),
                'updated_at' => Carbon::now()->subDays(2),
            ],
            [
                'user_id' => $userId,
                'subject' => 'Account Deletion Request',
                'message' => 'I would like to delete my account and all associated data. Please confirm the deletion process and timeline.',
                'status' => 'open',
                'created_at' => Carbon::now()->subHours(12),
                'updated_at' => Carbon::now()->subHours(12),
            ],
            [
                'user_id' => $userId,
                'subject' => 'Course Content Issue',
                'message' => 'Some videos in the Advanced JavaScript course are missing or corrupted. Specifically, modules 3 and 4 are not loading properly.',
                'status' => 'closed',
                'created_at' => Carbon::now()->subDays(5),
                'updated_at' => Carbon::now()->subDays(4),
            ],
            [
                'user_id' => $userId,
                'subject' => 'Instructor Response Time',
                'message' => 'I posted a question in the course forum 3 days ago and haven\'t received a response from the instructor. The course promises 24-hour response times.',
                'status' => 'pending',
                'created_at' => Carbon::now()->subDays(3),
                'updated_at' => Carbon::now()->subDays(3),
            ],
            [
                'user_id' => $userId,
                'subject' => 'Mobile App Bug Report',
                'message' => 'The iOS app crashes when I try to download videos for offline viewing. This happens on both iPhone 12 and iPad Pro running iOS 15.',
                'status' => 'open',
                'created_at' => Carbon::now()->subHours(8),
                'updated_at' => Carbon::now()->subHours(8),
            ],
            [
                'user_id' => $userId,
                'subject' => 'Subscription Cancellation',
                'message' => 'I want to cancel my monthly subscription but I can\'t find the option in my account settings. Please guide me through the process.',
                'status' => 'closed',
                'created_at' => Carbon::now()->subDays(7),
                'updated_at' => Carbon::now()->subDays(6),
            ],
        ]);
    }
}

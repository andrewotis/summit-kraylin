<?php

namespace Webkul\Installer\Database\Seeders\EmailTemplate;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EmailTemplateSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @param  array  $parameters
     * @return void
     */
    public function run($parameters = [])
    {
        DB::table('email_templates')->delete();

        $now = Carbon::now();

        $defaultLocale = $parameters['locale'] ?? config('app.locale');

        DB::table('email_templates')->insert([
            [
                'id' => 1,
                'name' => trans('installer::app.seeders.email.activity-created', [], $defaultLocale),
                'subject' => trans('installer::app.seeders.email.activity-created', [], $defaultLocale).': {%activities.title%}',
                'created_at' => $now,
                'updated_at' => $now,
                'content' => '<p style="font-size: 16px; color: #5e5e5e;">'.trans('installer::app.seeders.email.new-activity', [], $defaultLocale).':</p>
                                <p><strong style="font-size: 16px;">Details</strong></p>
                                <table style="height: 97px; width: 952px;">
                                    <tbody>
                                        <tr>
                                            <td style="width: 116.953px; color: #546e7a; font-size: 16px;">'.trans('installer::app.seeders.email.title', [], $defaultLocale).'</td>
                                            <td style="width: 770.047px; font-size: 16px;">{%activities.title%}</td>
                                        </tr>
                                        <tr>
                                            <td style="width: 116.953px; color: #546e7a; font-size: 16px;">'.trans('installer::app.seeders.email.type', [], $defaultLocale).'</td>
                                                <td style="width: 770.047px; font-size: 16px;">{%activities.type%}</td>
                                        </tr>
                                        <tr>
                                            <td style="width: 116.953px; color: #546e7a; font-size: 16px;">'.trans('installer::app.seeders.email.date', [], $defaultLocale).'</td>
                                            <td style="width: 770.047px; font-size: 16px;">{%activities.schedule_from%} to&nbsp;{%activities.schedule_to%}</td>
                                        </tr>
                                        <tr>
                                            <td style="width: 116.953px; color: #546e7a; font-size: 16px; vertical-align: text-top;">'.trans('installer::app.seeders.email.participants', [], $defaultLocale).'</td>
                                            <td style="width: 770.047px; font-size: 16px;">{%activities.participants%}</td>
                                        </tr>
                                    </tbody>
                                </table>',
            ], [
                'id' => 2,
                'name' => 'Summit Bed Availability Update',
                'subject' => 'Current bed availability at Summit 3.5 and 3.1, {%persons.name%}',
                'created_at' => $now,
                'updated_at' => $now,
                'content' => '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin:0; padding:0; background-color:#f4f4f5; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f4f5; padding: 32px 0;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color:#ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.08);">
                    <tr>
                        <td style="background: linear-gradient(135deg, #0b6e4f, #0891b2); padding: 32px; text-align: center;">
                            <img src="{%logo_url%}" alt="Summit Community Foundation" style="height: 50px; margin-bottom: 16px;">
                            <h1 style="margin: 0; font-size: 26px; color: #ffffff; font-weight: 700; letter-spacing: -0.5px;">Bed Availability Update</h1>
                            <p style="margin: 10px 0 0; font-size: 17px; color: rgba(255,255,255,0.9);">Summit 3.5 &amp; 3.1 Facilities</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 40px 32px;">
                            <p style="margin: 0 0 18px; font-size: 16px; color: #374151; line-height: 1.7;">Hi {%persons.name%},</p>
                            <p style="margin: 0 0 18px; font-size: 16px; color: #374151; line-height: 1.7;">Quick update on current bed availability across our Summit facilities:</p>
                            <table width="100%" cellpadding="0" cellspacing="0" style="margin: 0 0 24px; border-collapse: collapse;">
                                <tr>
                                    <td style="padding: 12px 16px; background-color: #f0fdf4; border-bottom: 1px solid #d1fae5; font-size: 15px; font-weight: 600; color: #065f46;">Summit 3.5</td>
                                    <td style="padding: 12px 16px; background-color: #f0fdf4; border-bottom: 1px solid #d1fae5; font-size: 15px; color: #065f46; text-align: right;"><strong>3 beds available</strong></td>
                                </tr>
                                <tr>
                                    <td style="padding: 12px 16px; font-size: 15px; font-weight: 600; color: #374151;">Summit 3.1</td>
                                    <td style="padding: 12px 16px; font-size: 15px; color: #374151; text-align: right;"><strong>5 beds available</strong></td>
                                </tr>
                            </table>
                            <p style="margin: 0 0 8px; font-size: 16px; color: #374151; line-height: 1.7;">To place a referral or check on specific levels of care, give us a call:</p>
                            <p style="margin: 0 0 24px; font-size: 18px; color: #0b6e4f; font-weight: 600; text-align: center;">(555) 555-5555</p>
                            <table cellpadding="0" cellspacing="0" style="margin: 0 auto 32px;">
                                <tr>
                                    <td align="center" style="background: linear-gradient(135deg, #0b6e4f, #0891b2); border-radius: 6px;">
                                        <a href="tel:5555555555" style="display: inline-block; padding: 14px 36px; font-size: 16px; font-weight: 600; color: #ffffff; text-decoration: none; letter-spacing: 0.3px;">Call to Place a Referral</a>
                                    </td>
                                </tr>
                            </table>
                            <hr style="border: none; border-top: 1px solid #e5e7eb; margin: 32px 0;">
                            <p style="margin: 0 0 4px; font-size: 14px; color: #9ca3af; line-height: 1.5;">Catonsville, MD</p>
                            <p style="margin: 0 0 8px; font-size: 14px; color: #9ca3af; line-height: 1.5;">If you\'d rather not receive future emails, you can <a href="{%unsubscribe_url%}" style="color: #2563eb; text-decoration: underline;">unsubscribe here</a>.</p>
                            <p style="margin: 0; font-size: 13px; color: #9ca3af;">&copy; 2026 Summit Community Foundation. All rights reserved.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>',
            ], [
                'id' => 3,
                'name' => trans('installer::app.seeders.email.activity-modified', [], $defaultLocale),
                'subject' => trans('installer::app.seeders.email.activity-modified', [], $defaultLocale).': {%activities.title%}',
                'created_at' => $now,
                'updated_at' => $now,
                'content' => '<p style="font-size: 16px; color: #5e5e5e;">'.trans('installer::app.seeders.email.new-activity-modified', [], $defaultLocale).':</p>
                                <p><strong style="font-size: 16px;">Details</strong></p>
                                <table style="height: 97px; width: 952px;">
                                    <tbody>
                                        <tr>
                                            <td style="width: 116.953px; color: #546e7a; font-size: 16px;">'.trans('installer::app.seeders.email.title', [], $defaultLocale).'</td>
                                            <td style="width: 770.047px; font-size: 16px;">{%activities.title%}</td>
                                        </tr>
                                        <tr>
                                            <td style="width: 116.953px; color: #546e7a; font-size: 16px;">'.trans('installer::app.seeders.email.type', [], $defaultLocale).'</td>
                                            <td style="width: 770.047px; font-size: 16px;">{%activities.type%}</td>
                                        </tr>
                                        <tr>
                                            <td style="width: 116.953px; color: #546e7a; font-size: 16px;">'.trans('installer::app.seeders.email.date', [], $defaultLocale).'</td>
                                            <td style="width: 770.047px; font-size: 16px;">{%activities.schedule_from%} to&nbsp;{%activities.schedule_to%}</td>
                                        </tr>
                                        <tr>
                                            <td style="width: 116.953px; color: #546e7a; font-size: 16px; vertical-align: text-top;">'.trans('installer::app.seeders.email.participants', [], $defaultLocale).'</td>
                                            <td style="width: 770.047px; font-size: 16px;">{%activities.participants%}</td>
                                        </tr>
                                    </tbody>
                                </table>',
            ], [
                'id' => 4,
                'name' => 'Welcome to Summit Community',
                'subject' => 'Welcome to Summit Community, {%persons.name%}',
                'created_at' => $now,
                'updated_at' => $now,
                'content' => '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin:0; padding:0; background-color:#f4f4f5; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f4f5; padding: 32px 0;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color:#ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.08);">
                    <tr>
                        <td style="background: linear-gradient(135deg, #0b6e4f, #0891b2); padding: 32px; text-align: center;">
                            <img src="{%logo_url%}" alt="Summit Community Foundation" style="height: 50px; margin-bottom: 16px;">
                            <h1 style="margin: 0; font-size: 26px; color: #ffffff; font-weight: 700; letter-spacing: -0.5px;">Welcome to Summit Community</h1>
                            <p style="margin: 8px 0 0; font-size: 17px; color: rgba(255,255,255,0.9);">We\'re glad to have you</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 40px 32px;">
                            <p style="margin: 0 0 18px; font-size: 16px; color: #374151; line-height: 1.7;">Dear {%persons.name%},</p>
                            <p style="margin: 0 0 18px; font-size: 16px; color: #374151; line-height: 1.7;">Welcome to Summit Community Foundation! We are honored that you have chosen us to be part of your journey toward recovery and wellness.</p>
                            <p style="margin: 0 0 18px; font-size: 16px; color: #374151; line-height: 1.7;">Our team is committed to providing compassionate, evidence-based care in a safe and supportive environment. Here is what you can expect:</p>
                            <table width="100%" cellpadding="0" cellspacing="0" style="margin: 0 0 24px;">
                                <tr>
                                    <td style="padding: 14px 18px; background-color: #f0fdf4; border-left: 4px solid #0b6e4f; margin-bottom: 8px; font-size: 15px; color: #374151; line-height: 1.6;">
                                        <strong style="color: #065f46;">Personalized Care Plans</strong> &mdash; Tailored to meet your unique needs and goals
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 14px 18px; background-color: #f0fdf4; border-left: 4px solid #0891b2; margin-bottom: 8px; font-size: 15px; color: #374151; line-height: 1.6;">
                                        <strong style="color:#155e75;">24/7 Support</strong> &mdash; Round-the-clock care from our dedicated staff
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 14px 18px; background-color: #f0fdf4; border-left: 4px solid #0b6e4f; font-size: 15px; color: #374151; line-height: 1.6;">
                                        <strong style="color: #065f46;">Holistic Approach</strong> &mdash; Addressing mind, body, and spirit in recovery
                                    </td>
                                </tr>
                            </table>
                            <p style="margin: 0 0 24px; font-size: 16px; color: #374151; line-height: 1.7;">If you have any questions or need assistance, please don\'t hesitate to reach out. We are here for you every step of the way.</p>
                            <table cellpadding="0" cellspacing="0" style="margin: 0 auto 32px;">
                                <tr>
                                    <td align="center" style="background: linear-gradient(135deg, #0b6e4f, #0891b2); border-radius: 6px;">
                                        <a href="tel:5555555555" style="display: inline-block; padding: 14px 36px; font-size: 16px; font-weight: 600; color: #ffffff; text-decoration: none; letter-spacing: 0.3px;">Contact Us</a>
                                    </td>
                                </tr>
                            </table>
                            <hr style="border: none; border-top: 1px solid #e5e7eb; margin: 32px 0;">
                            <p style="margin: 0 0 4px; font-size: 14px; color: #9ca3af; line-height: 1.5;">Catonsville, MD</p>
                            <p style="margin: 0 0 8px; font-size: 14px; color: #9ca3af; line-height: 1.5;">If you\'d rather not receive future emails, you can <a href="{%unsubscribe_url%}" style="color: #2563eb; text-decoration: underline;">unsubscribe here</a>.</p>
                            <p style="margin: 0; font-size: 13px; color: #9ca3af;">&copy; 2026 Summit Community Foundation. All rights reserved.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>',
            ], [
                'id' => 5,
                'name' => 'Program Update',
                'subject' => 'Program Update from Summit Community, {%persons.name%}',
                'created_at' => $now,
                'updated_at' => $now,
                'content' => '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin:0; padding:0; background-color:#f4f4f5; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f4f5; padding: 32px 0;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color:#ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.08);">
                    <tr>
                        <td style="background: linear-gradient(135deg, #0b6e4f, #0891b2); padding: 32px; text-align: center;">
                            <img src="{%logo_url%}" alt="Summit Community Foundation" style="height: 50px; margin-bottom: 16px;">
                            <h1 style="margin: 0; font-size: 26px; color: #ffffff; font-weight: 700; letter-spacing: -0.5px;">Program Update</h1>
                            <p style="margin: 8px 0 0; font-size: 17px; color: rgba(255,255,255,0.9);">Latest news from Summit Community Foundation</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 40px 32px;">
                            <p style="margin: 0 0 18px; font-size: 16px; color: #374151; line-height: 1.7;">Dear {%persons.name%},</p>
                            <p style="margin: 0 0 18px; font-size: 16px; color: #374151; line-height: 1.7;">We wanted to share some exciting updates happening here at Summit Community Foundation. Our commitment to providing the highest quality care continues to drive us forward.</p>
                            <table width="100%" cellpadding="0" cellspacing="0" style="margin: 0 0 24px; border-collapse: collapse;">
                                <tr>
                                    <td style="padding: 16px; background-color: #f0fdf4; border: 1px solid #d1fae5; border-radius: 6px;">
                                        <h3 style="margin: 0 0 8px; font-size: 17px; color: #065f46;">New Group Therapy Sessions</h3>
                                        <p style="margin: 0; font-size: 15px; color: #374151; line-height: 1.6;">We have expanded our group therapy offerings to include evening sessions for added flexibility. Topics include mindfulness, relapse prevention, and family support.</p>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 16px; background-color: #ecfeff; border: 1px solid #cffafe; border-radius: 6px;">
                                        <h3 style="margin: 0 0 8px; font-size: 17px; color: #155e75;">Expanded Bed Capacity</h3>
                                        <p style="margin: 0; font-size: 15px; color: #374151; line-height: 1.6;">Our Summit 3.5 facility has added additional beds to better serve our growing community. Referrals are welcome &mdash; call us for current availability.</p>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 16px; background-color: #f0fdf4; border: 1px solid #d1fae5; border-radius: 6px;">
                                        <h3 style="margin: 0 0 8px; font-size: 17px; color: #065f46;">Aftercare Program Launch</h3>
                                        <p style="margin: 0; font-size: 15px; color: #374151; line-height: 1.6;">Our new aftercare program provides continued support for individuals transitioning back to daily life, with regular check-ins and community resources.</p>
                                    </td>
                                </tr>
                            </table>
                            <p style="margin: 0 0 24px; font-size: 16px; color: #374151; line-height: 1.7;">For more information about any of these updates, please give us a call or visit our website.</p>
                            <table cellpadding="0" cellspacing="0" style="margin: 0 auto 32px;">
                                <tr>
                                    <td align="center" style="background: linear-gradient(135deg, #0b6e4f, #0891b2); border-radius: 6px;">
                                        <a href="tel:5555555555" style="display: inline-block; padding: 14px 36px; font-size: 16px; font-weight: 600; color: #ffffff; text-decoration: none; letter-spacing: 0.3px;">Learn More</a>
                                    </td>
                                </tr>
                            </table>
                            <hr style="border: none; border-top: 1px solid #e5e7eb; margin: 32px 0;">
                            <p style="margin: 0 0 4px; font-size: 14px; color: #9ca3af; line-height: 1.5;">Catonsville, MD</p>
                            <p style="margin: 0 0 8px; font-size: 14px; color: #9ca3af; line-height: 1.5;">If you\'d rather not receive future emails, you can <a href="{%unsubscribe_url%}" style="color: #2563eb; text-decoration: underline;">unsubscribe here</a>.</p>
                            <p style="margin: 0; font-size: 13px; color: #9ca3af;">&copy; 2026 Summit Community Foundation. All rights reserved.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>',
            ], [
                'id' => 6,
                'name' => 'You\'re Invited: Summit Community Event',
                'subject' => 'You\'re Invited, {%persons.name%} — Summit Community Foundation Event',
                'created_at' => $now,
                'updated_at' => $now,
                'content' => '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin:0; padding:0; background-color:#f4f4f5; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f4f5; padding: 32px 0;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color:#ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.08);">
                    <tr>
                        <td style="background: linear-gradient(135deg, #0b6e4f, #0891b2); padding: 32px; text-align: center;">
                            <img src="{%logo_url%}" alt="Summit Community Foundation" style="height: 50px; margin-bottom: 16px;">
                            <h1 style="margin: 0; font-size: 26px; color: #ffffff; font-weight: 700; letter-spacing: -0.5px;">You\'re Invited</h1>
                            <p style="margin: 8px 0 0; font-size: 17px; color: rgba(255,255,255,0.9);">Summit Community Foundation Event</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 40px 32px;">
                            <p style="margin: 0 0 18px; font-size: 16px; color: #374151; line-height: 1.7;">Dear {%persons.name%},</p>
                            <p style="margin: 0 0 18px; font-size: 16px; color: #374151; line-height: 1.7;">We are pleased to invite you to an upcoming event hosted by Summit Community Foundation. Join us for an evening of connection, learning, and community building.</p>
                            <table width="100%" cellpadding="0" cellspacing="0" style="margin: 0 0 24px; border-collapse: collapse; border: 1px solid #e5e7eb; border-radius: 8px;">
                                <tr>
                                    <td style="padding: 20px; text-align: center;">
                                        <p style="margin: 0 0 12px; font-size: 20px; font-weight: 700; color: #0b6e4f;">Recovery &amp; Wellness Open House</p>
                                        <p style="margin: 0 0 6px; font-size: 15px; color: #374151;"><strong>Date:</strong> TBD</p>
                                        <p style="margin: 0 0 6px; font-size: 15px; color: #374151;"><strong>Time:</strong> TBD</p>
                                        <p style="margin: 0 0 6px; font-size: 15px; color: #374151;"><strong>Location:</strong> Summit Community Foundation, Catonsville, MD</p>
                                        <p style="margin: 12px 0 0; font-size: 15px; color: #374151; line-height: 1.6;">Enjoy refreshments, meet our team, tour our facilities, and learn about our programs. Bring a friend or family member!</p>
                                    </td>
                                </tr>
                            </table>
                            <p style="margin: 0 0 24px; font-size: 16px; color: #374151; line-height: 1.7;">Space is limited, so please RSVP at your earliest convenience. We would love to see you there.</p>
                            <table cellpadding="0" cellspacing="0" style="margin: 0 auto 32px;">
                                <tr>
                                    <td align="center" style="background: linear-gradient(135deg, #0b6e4f, #0891b2); border-radius: 6px;">
                                        <a href="tel:5555555555" style="display: inline-block; padding: 14px 36px; font-size: 16px; font-weight: 600; color: #ffffff; text-decoration: none; letter-spacing: 0.3px;">RSVP Today</a>
                                    </td>
                                </tr>
                            </table>
                            <hr style="border: none; border-top: 1px solid #e5e7eb; margin: 32px 0;">
                            <p style="margin: 0 0 4px; font-size: 14px; color: #9ca3af; line-height: 1.5;">Catonsville, MD</p>
                            <p style="margin: 0 0 8px; font-size: 14px; color: #9ca3af; line-height: 1.5;">If you\'d rather not receive future emails, you can <a href="{%unsubscribe_url%}" style="color: #2563eb; text-decoration: underline;">unsubscribe here</a>.</p>
                            <p style="margin: 0; font-size: 13px; color: #9ca3af;">&copy; 2026 Summit Community Foundation. All rights reserved.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>',
            ], [
                'id' => 7,
                'name' => 'Checking In — Summit Community Foundation',
                'subject' => 'Checking In, {%persons.name%} — How are you doing?',
                'created_at' => $now,
                'updated_at' => $now,
                'content' => '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin:0; padding:0; background-color:#f4f4f5; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f4f5; padding: 32px 0;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color:#ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.08);">
                    <tr>
                        <td style="background: linear-gradient(135deg, #0b6e4f, #0891b2); padding: 32px; text-align: center;">
                            <img src="{%logo_url%}" alt="Summit Community Foundation" style="height: 50px; margin-bottom: 16px;">
                            <h1 style="margin: 0; font-size: 26px; color: #ffffff; font-weight: 700; letter-spacing: -0.5px;">Thinking of You</h1>
                            <p style="margin: 8px 0 0; font-size: 17px; color: rgba(255,255,255,0.9);">A quick check-in from Summit Community Foundation</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 40px 32px;">
                            <p style="margin: 0 0 18px; font-size: 16px; color: #374151; line-height: 1.7;">Dear {%persons.name%},</p>
                            <p style="margin: 0 0 18px; font-size: 16px; color: #374151; line-height: 1.7;">We hope this message finds you well. At Summit Community Foundation, we believe that recovery is a journey, and no one should walk it alone.</p>
                            <p style="margin: 0 0 18px; font-size: 16px; color: #374151; line-height: 1.7;">We wanted to take a moment to check in and let you know that our doors are always open. Whether you need support, have questions, or just want to talk, we are here for you.</p>
                            <table width="100%" cellpadding="0" cellspacing="0" style="margin: 0 0 24px; background-color: #f0fdf4; border-radius: 8px;">
                                <tr>
                                    <td style="padding: 20px; text-align: center;">
                                        <p style="margin: 0 0 10px; font-size: 16px; color: #065f46; font-weight: 600;">Need Support?</p>
                                        <p style="margin: 0 0 4px; font-size: 22px; color: #0b6e4f; font-weight: 700;">(555) 555-5555</p>
                                        <p style="margin: 0; font-size: 14px; color: #6b7280;">Available 24/7</p>
                                    </td>
                                </tr>
                            </table>
                            <p style="margin: 0 0 8px; font-size: 16px; color: #374151; line-height: 1.7;">Remember, reaching out is a sign of strength. We are proud of the progress you\'ve made and are honored to be part of your journey.</p>
                            <p style="margin: 0 0 24px; font-size: 16px; color: #374151; line-height: 1.7;">With care,<br>The Summit Community Foundation Team</p>
                            <table cellpadding="0" cellspacing="0" style="margin: 0 auto 32px;">
                                <tr>
                                    <td align="center" style="background: linear-gradient(135deg, #0b6e4f, #0891b2); border-radius: 6px;">
                                        <a href="tel:5555555555" style="display: inline-block; padding: 14px 36px; font-size: 16px; font-weight: 600; color: #ffffff; text-decoration: none; letter-spacing: 0.3px;">Call Us</a>
                                    </td>
                                </tr>
                            </table>
                            <hr style="border: none; border-top: 1px solid #e5e7eb; margin: 32px 0;">
                            <p style="margin: 0 0 4px; font-size: 14px; color: #9ca3af; line-height: 1.5;">Catonsville, MD</p>
                            <p style="margin: 0 0 8px; font-size: 14px; color: #9ca3af; line-height: 1.5;">If you\'d rather not receive future emails, you can <a href="{%unsubscribe_url%}" style="color: #2563eb; text-decoration: underline;">unsubscribe here</a>.</p>
                            <p style="margin: 0; font-size: 13px; color: #9ca3af;">&copy; 2026 Summit Community Foundation. All rights reserved.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>',
            ],
        ]);
    }
}

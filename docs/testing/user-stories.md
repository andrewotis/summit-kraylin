# Summit Krayin CRM — QA / User Stories

**Purpose:** Manual test scenarios for validating CRM functionality before releases.
Each story describes a user-facing capability, its acceptance criteria, and edge
cases to verify.

**Stack:** Laravel 12, PHP 8.3, MySQL 8.0+, Vue.js 3 + Vee-Validate 4
**Reference:** [ROADMAP.md](../../ROADMAP.md) for infrastructure & feature context

---

## 1. Authentication & Users

### 1.1 Admin Login
**As an** admin user  
**I want to** log in with my email and password  
**So that** I can access the CRM dashboard

**Acceptance criteria:**
- Login form accepts email and password
- Valid credentials redirect to dashboard
- Invalid credentials show "Invalid credentials" error
- Empty email or password shows inline validation error
- Throttle: after 5 failed attempts, account is locked for 60 seconds

**Edge cases:**
- Login with deactivated user → rejected
- Login with 2FA enabled → redirects to 2FA challenge
- Session expiry → redirects to login
- Idle timeout (15 min) → auto-logout with message

### 1.2 Create User
**As an** admin user  
**I want to** create a new CRM user with a role  
**So that** they can access the system with appropriate permissions

**Acceptance criteria:**
- Form requires: name, email, password, password confirmation, role
- Email must be unique (duplicate shows validation error)
- Password must meet complexity rules (min length, etc.)
- Password and confirmation must match
- Role dropdown shows all available roles
- User is created and appears in the users list
- New user receives no notification email (just created)

**Edge cases:**
- Create user with already-used email → error
- Empty role → validation error (role is required)
- Create and then immediately log in as new user → works
- Delete user → removed from list, cannot log in

---

## 2. Contacts — Organizations

### 2.1 Create Organization
**As a** CRM user  
**I want to** create an organization record  
**So that** I can track companies my contacts belong to

**Acceptance criteria:**
- Form fields: name (required), address, sales owner (lookup)
- Name is required and trimmed
- Address is optional
- Sales owner is a lookup against users
- Submission redirects to organization view/index with success flash
- Organization appears in search results

**Edge cases:**
- Create with only name → succeeds
- Create with special characters in name → succeeds
- Create with a very long name (>255 chars) → truncated or validation error
- Organization name encryption → stored encrypted, decrypted on view

### 2.2 Edit Organization
**As a** CRM user  
**I want to** update an existing organization's details  
**So that** information stays current

**Acceptance criteria:**
- Edit form is pre-filled with current values
- Name is required
- Changes persist after save
- Cancel returns to view without changes

**Edge cases:**
- Clear name field → validation error (required)
- Change sales owner → updates correctly
- No changes made → save does nothing (or succeeds with no diff)

### 2.3 View Organization
**As a** CRM user  
**I want to** view an organization's full details  
**So that** I can see related persons and metadata

**Acceptance criteria:**
- Shows: name, address, sales owner, created date
- Shows list of associated persons (linked to this org)
- Shows activities related to the organization
- (Future) Shows tags attached to the organization

### 2.4 Search/Filter Organizations
**As a** CRM user  
**I want to** search organizations by name  
**So that** I can quickly find the right one

**Acceptance criteria:**
- Search bar filters as I type
- Results update in the datagrid
- Empty search returns all (paginated)
- Non-matching search shows empty state

### 2.5 Delete Organization
**As a** CRM user  
**I want to** delete an organization  
**So that** I can remove outdated or duplicate records

**Acceptance criteria:**
- Delete requires confirmation (modal)
- Organization is soft-deleted or hard-deleted
- Persons linked to the organization are unlinked (org_id = null)
- Cannot delete while persons are still linked? (check current behavior)

**Edge cases:**
- Delete org with 20 persons → persons unlinked
- Delete org, then undo (if soft-delete) → restore works
- Mass-delete selected organizations

---

## 3. Contacts — Persons

### 3.1 Create Person
**As a** CRM user  
**I want to** create a person record  
**So that** I can track individuals for outreach and lead management

**Acceptance criteria:**
- Form fields: name (required), emails (required, at least one), contact numbers
  (optional), job title (optional), sales owner (lookup), organization (lookup)
- Name appears in the persons datagrid after creation
- Email is required and must be valid format
- Duplicate email (unique constraint) shows validation error
- Organization can be selected from existing or typed to create on-the-fly
- Sales owner is a lookup against users
- Redirects to persons index with success flash

**Edge cases:**
- Create with one email → works
- Create with multiple emails (add more) → all saved
- Create with duplicate email across persons → error
- Create with no email → validation error
- Create with special characters in name → succeed (encrypted)
- Create with phone number → formatted correctly on view
- Create without selecting an organization → org is null
- Create with new organization name → org created on the fly
- Form validation prevents submission with invalid data
- Long job title (>100 chars) → validation error
- Encryption: name, emails, contact_numbers stored encrypted

### 3.2 Edit Person
**As a** CRM user  
**I want to** update a person's details  
**So that** information stays current

**Acceptance criteria:**
- Edit form pre-filled with current values
- Email changes trigger unique validation (can't use another person's email)
- Organization can be changed
- Same validation rules as create
- Changes persist after save

**Edge cases:**
- Remove all emails → validation error (at least one required)
- Change email to one already used by another person → error
- Change organization → person moves to new org
- No changes → save succeeds without side effects

### 3.3 View Person
**As a** CRM user  
**I want to** view a person's full profile  
**So that** I can see their details, leads, activities, and tags

**Acceptance criteria:**
- Shows: name, email(s), phone(s), job title, sales owner, organization
- Shows related leads (if any)
- Shows activities timeline
- Shows attached tags with colors
- Tags can be added/removed inline on the view page

### 3.4 Search Persons
**As a** CRM user  
**I want to** search persons by name, email, or phone  
**So that** I can quickly find contacts

**Acceptance criteria:**
- Search by partial name returns matches
- Search by email (partial or full) returns matches
- Search by phone (partial or full) returns matches
- Results paginated
- Empty state when no matches

### 3.5 Delete Person
**As a** CRM user  
**I want to** delete a person record  
**So that** I can remove duplicates or incorrect entries

**Acceptance criteria:**
- Delete requires confirmation
- Person is removed from datagrid
- Person removed from associated mailing list subscribers

**Edge cases:**
- Delete person linked to a lead → blocked with message
- Delete person with no leads → succeeds
- Mass-delete selected persons (respects lead constraints)

---

## 4. Email Templates

### 4.1 Create Email Template
**As a** CRM user  
**I want to** create an email template with placeholders  
**So that** I can send personalized campaign emails

**Acceptance criteria:**
- Form fields: name (required), subject (required), content (required, rich text)
- Placeholders like `{%persons.name%}`, `{%persons.emails.0.value%}` are supported
- Template is saved and appears in the templates list
- Content uses TinyMCE rich text editor

**Edge cases:**
- Template with no placeholders → renders as-is
- Template with invalid placeholders → renders as-is (no crash)
- Very long subject line → stored/displayed correctly
- HTML in content → preserved

### 4.2 Edit Email Template
**As a** CRM user  
**I want to** update an existing template  
**So that** I can refine messaging

**Acceptance criteria:**
- Form is pre-filled
- Changes to placeholders propagate on next campaign send
- Validation same as create

### 4.3 Delete Email Template
**As a** CRM user  
**I want to** delete an unused email template  
**So that** the template list stays clean

**Acceptance criteria:**
- Delete requires confirmation
- Cannot delete template linked to an active campaign? (check current behavior)
- Template removed from list

---

## 5. Tags (Existing — Persons, Leads, Products)

### 5.1 Create Tag
**As a** CRM user  
**I want to** create a colored tag in Settings > Tags  
**So that** I can categorize contacts and leads

**Acceptance criteria:**
- Tag has a name and a color
- Name is required
- Color picker allows selecting from predefined colors
- Tag appears in Settings > Tags list
- Tag can be edited (rename, recolor) and deleted

### 5.2 Tag a Person
**As a** CRM user  
**I want to** attach/detach tags on a person's profile  
**So that** I can categorize them

**Acceptance criteria:**
- Person view shows existing tags as colored badges
- Click to add opens tag search/selector
- Can search existing tags or create new ones on the fly
- Tags are saved immediately (auto-save on select)
- Click X on a badge to detach
- Tag appears/disappears without page reload

### 5.3 Filter/Find by Tag
**As a** CRM user  
**I want to** find all persons with a specific tag  
**So that** I can work with a targeted subset

**Acceptance criteria:**
- (TODO: implement if not present) Tag column or filter in persons datagrid
- Clicking a tag badge could filter by that tag
- No-currently: this is a gap — tags are visible on the person view but not filterable in the datagrid

---

## 6. Mailing Lists (Custom Package)

### 6.1 Create Mailing List
**As a** CRM user  
**I want to** create a named mailing list  
**So that** I can group contacts for targeted campaigns

**Acceptance criteria:**
- Form: name (required), description (optional)
- Name must be unique
- List appears in Settings > Mailing Lists

### 6.2 Add Subscribers to Mailing List
**As a** CRM user  
**I want to** add persons to a mailing list  
**So that** they receive campaign emails

**Acceptance criteria:**
- Subscriber form: person lookup, auto-subscribed by default
- Person must exist in the system
- Duplicate person in list → handled gracefully (no duplicate entry)
- Subscribers tab shows all members with subscription status
- Can toggle `is_subscribed` on/off
- Can remove subscriber from list

**Edge cases:**
- Add same person twice → prevented or idempotent
- Subscribe/unsubscribe/re-subscribe → status toggles correctly
- Bulk add multiple persons? (check current)

### 6.3 Delete Mailing List
**As a** CRM user  
**I want to** delete a mailing list  
**So that** I can clean up unused lists

**Acceptance criteria:**
- Delete removes all subscriber associations
- Campaigns referencing this list should handle deletion gracefully (nullable FK or cascade)
- Confirmation required

---

## 7. Marketing Campaigns (Custom Package)

### 7.1 Create Campaign
**As a** CRM user  
**I want to** create a marketing campaign with an email template and target list  
**So that** I can send bulk emails

**Acceptance criteria:**
- Form: name (required), subject, status (active/inactive), email template
  (dropdown), event (dropdown), mailing list (dropdown)
- Email template dropdown loads existing templates
- Event dropdown loads existing marketing events
- Mailing list dropdown loads existing mailing lists
- If no mailing list selected, campaign sends to all persons
- Campaign is saved as inactive by default

**Edge cases:**
- Create campaign without mailing list → sends to all persons (check desired behavior)
- Create campaign with an inactive email template → warns or prevents
- Edit campaign — campaign persists changes

### 7.2 Send Campaign
**As a** system  
**I want to** process active campaigns via `campaign:process` artisan command  
**So that** queued emails are dispatched

**Acceptance criteria:**
- Command picks up active campaigns whose event date has passed
- Renders email template with person-specific placeholders
- Respects mailing list subscriber filter
- Queues emails for async delivery (when Redis is configured)
- Unsubscribe URL is injected into email body
- Command output shows: campaign name, recipients count, queued count

**Edge cases:**
- Campaign with no subscribers → processed, 0 emails sent
- Campaign with 1000 subscribers → all queued
- Person with no email → skipped
- Placeholder for missing attribute → renders empty string (no crash)
- Command run twice → should not send duplicates (mark as sent)

### 7.3 One-Click Unsubscribe
**As a** campaign recipient  
**I want to** click unsubscribe in a campaign email  
**So that** I stop receiving future emails

**Acceptance criteria:**
- Unsubscribe link is present in every campaign email
- Link contains HMAC-signed token (person_id + mailing_list_id)
- Clicking link opens confirmation page (no login required)
- Confirming sets `is_subscribed = false` for that person+list
- Subsequent campaigns skip this person
- Confirmation page shows "You have been unsubscribed" message

**Edge cases:**
- Click unsubscribe for already-unsubscribed person → shows "already unsubscribed"
- Tampered token → shows "invalid link" error
- Expired token → shows "link expired" error
- Unsubscribe from all lists? (Phase 2 feature)

---

## 8. Tag-Based Email Targeting (Phase 6 — Not Yet Built)

### 8.1 Tag Organizations
**As a** CRM user  
**I want to** tag organizations the same way I tag persons  
**So that** I can categorize both types of contacts

**Acceptance criteria:**
- (TODO) Organization view shows tag component
- (TODO) Can attach/detach tags on orgs
- (TODO) Tags are searchable and creatable on the fly

### 8.2 Filter Mailing List by Tag
**As a** CRM user  
**I want to** create a mailing list that auto-includes persons with specific tags  
**So that** the list stays current without manual subscriber management

**Acceptance criteria:**
- (TODO) Mailing list create/edit form has "Tag filter" section
- (TODO) Can select one or more tags (person tags)
- (TODO) Optionally select organization tags (includes persons in tagged orgs)
- (TODO) Subscribers are resolved dynamically: all persons matching the tag
  criteria are considered subscribers
- (TODO) Persons added later with matching tags are automatically included
- (TODO) Persons whose tags change are automatically included/excluded

### 8.3 Campaign Respects Tag Filters
**As a** system  
**I want to** resolve tag-filtered mailing lists during campaign processing  
**So that** only tagged persons receive the email

**Acceptance criteria:**
- (TODO) `Campaign::getPersons()` resolves tag filters when present
- (TODO) Person tags and organization tags are both evaluated
- (TODO) Subscriber's `is_subscribed` flag is still respected on top of tag
  filtering (opt-out override)

---

## 9. Regression / Cross-Cutting

### 9.1 Navigation & Menu
- All menu items in sidebar load correct pages
- Breadcrumbs reflect current page hierarchy
- Responsive: sidebar collapses on narrow screens

### 9.2 Permissions / ACL
- User with limited role cannot access unauthorized sections
- Settings menu hidden from non-admin users
- Person/lead/organization CRUD respects role-based access

### 9.3 Encryption
- PHI fields (name, email, phone, address) stored encrypted in database
- Decrypted correctly on view/edit
- Audit logs show `[ENCRYPTED]` for encrypted fields

### 9.4 Two-Factor Auth
- Enabling 2FA shows QR code and recovery codes
- Login with 2FA challenges for TOTP code
- Recovery code works once, then consumed
- Emergency access works (admin grants time-limited token)

### 9.5 Error Handling
- 404/403 pages display gracefully
- Server validation errors show inline next to fields
- Network errors show flash message (not white screen)
- Form state preserved on validation error (user doesn't retype everything)

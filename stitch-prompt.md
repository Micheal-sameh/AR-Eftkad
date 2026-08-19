# Stitch Prompt — AR-Eftkad Frontend

Design a mobile-first web app called **"Eftkad"** (افتقاد) for a Coptic Orthodox church, used by priests ("Fathers") and lay ministers ("Servants") to log and browse pastoral home/call visits to congregation members. Support both Arabic (RTL, default) and English (LTR). Clean, calm, warm church-app aesthetic — soft neutral background, one accent color (deep gold or maroon), generous spacing, rounded cards, legible Arabic typography.

## Screens

1. **Login**
   - Fields: Membership Code (e.g. `E1C1F1NR1`), Password
   - "Sign in" primary button
   - Simple centered card layout, app name/logo at top

2. **Visits List (Home)** — `GET /eftkads`
   - Header with app name, language toggle (AR/EN), user avatar/menu with logout
   - Search/filter bar: filter by Father, by Servant (from `/settings/filters`), by visit type (Call / Home), by date range
   - Paginated list of visit cards, each showing: member membership code, visit date, visit type badge (Call/Home), mass attendance badge (Regular/Irregular/Unknown), location, small icons for needs and communication means
   - Floating action button "+" to add a new visit
   - Empty state illustration when no visits match filters

3. **New Visit Form** — `POST /eftkads`
   - Sectioned form:
     - Basic info: Member membership code, date picker, correspondence address, location, visit type (Call/Home toggle)
     - Attendance & confession: mass attendance (Regular/Irregular/Unknown radio), father confession / mother confession / children confession toggles
     - Needs: multi-select chips — Seniors, Patient, Job, Studying Help, Tnawel (home communion), Emergency Calls
     - Communication means: multi-select chips — WhatsApp CMC, Facebook Group, Kenisty App, No Communication
     - Meeting info: attend meetings toggle, need Eftkad from meeting toggle, need Eftkad by Father toggle
     - Visited by: Father membership code, Servant membership code
     - General notes: textarea
   - "Save Visit" primary button, sticky at bottom on mobile

4. **Visit Detail**
   - Read-only view of a single visit record, same grouped sections as the form, with edit/delete actions (if permitted)

5. **Settings / Enums reference (optional)**
   - Simple list screens showing Fathers and Servants (from filters), used as a lookup/reference

## Design notes

- Mobile-first responsive layout (this is used in the field during visits), but should also work well on tablet/desktop
- Use badges/chips with distinct colors for enum values (visit type, attendance status, needs)
- Support RTL layout mirroring for Arabic as the default locale
- Keep forms broken into clear collapsible sections given the large number of fields
- Include a bottom nav or sidebar with: Visits, New Visit, Settings/Filters, Logout

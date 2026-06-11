# AI Integration Documentation

## Overview

CiviSense uses **Google Gemini AI** exclusively for all AI-powered features. Two packages are used:

| Layer | Package | Version | Source |
|-------|---------|---------|--------|
| PHP (Backend) | `google-gemini-php/laravel` | ^2.0 | [GitHub](https://github.com/google-gemini-php/laravel) |
| JavaScript (Frontend) | `@google/generative-ai` | ^0.24.1 | [npm](https://www.npmjs.com/package/@google/generative-ai) |

---

## Configuration

**Required environment variable:**
```
GEMINI_API_KEY=your_key_here
```
Get your API key from [Google AI Studio](https://aistudio.google.com/app/apikey).

**Config file:** `config/gemini.php`

---

## 1. Complaint AI Analysis (Backend — PHP)

**Files:**
- `app/Services/ComplaintAiService.php`
- `app/Http/Controllers/ComplaintController.php`
- `app/Models/ComplaintAiAnalysis.php`
- `database/migrations/2026_06_10_112010_create_complaint_ai_analyses_table.php`

**Model:** `gemini-2.0-flash`

**What it does:**
When a user submits a complaint, the AI automatically:
- **Spam detection** — Checks if the title/description/attached images describe a real civic problem
- **Department & category assignment** — Maps the complaint to the correct municipal department and category
- **Priority classification** — Assigns low / medium / high / emergency
- **Summary generation** — Creates a clean technical summary under 200 characters
- **Image analysis** — Analyzes up to 3 attached images to verify the issue

**Flow:**
1. `POST /complaints` → `ComplaintController@store()`
2. First, a deterministic local spam filter runs (regex-based, 300+ civic keywords)
3. Then `ComplaintAiService::analyzeComplaint()` sends the data to Gemini 2.0 Flash with a structured JSON schema
4. If AI is unavailable, falls back to keyword-based priority and truncation-based summary
5. Results are stored in `complaint_ai_analyses` table via the `aiAnalysis()` relationship

---

## 2. Voice Commands (Backend + Frontend)

**Backend files:**
- `app/Services/VoiceCommandService.php`
- `app/Http/Controllers/VoiceCommandController.php`
- `routes/web.php` → `POST /voice/interpret`

**Frontend files:**
- `resources/js/Composables/useVoiceCommands.js`
- `resources/js/Composables/voiceCommandInterpreter.js`
- `resources/js/Composables/useVoiceContext.js`
- `resources/js/Composables/useSpeechRecognition.js`
- `resources/js/Components/VoiceGlobalAssistant.vue`
- `resources/js/Components/VoiceCommandPanel.vue`
- `resources/js/Components/VoiceInputButton.vue`

**Model:** `gemini-2.5-flash`

**What it does:**
Enables hands-free operation of the entire application via voice:

1. **Speech Recognition** — Uses the Web Speech API (`useSpeechRecognition.js`) to transcribe microphone input
2. **Local Interpretation** — `voiceCommandInterpreter.js` tries to match commands locally (bilingual English/Nepali regex patterns)
3. **Gemini Interpretation** — If local matching fails, sends transcript to Gemini 2.5 Flash for natural language understanding
4. **Action Execution** — The interpreted action is executed: navigate to pages, fill form fields, submit forms, search, filter, change language, etc.
5. **Global Assistant UI** — `VoiceGlobalAssistant.vue` provides a floating button (`Ctrl+Shift+V`) and panel throughout the app

**Supported actions:** `navigate`, `fill_field`, `submit_form`, `search`, `filter`, `clear_filters`, `open_create`, `change_language`, `logout`, `go_back`, `show_help`

---

## 3. Frontend AI Composable (Browser-side)

**File:** `resources/js/Composables/useGeminiAI.js`

**Model:** `gemini-2.0-flash`

**What it does:**
A reusable frontend composable for direct Gemini API calls from the browser:
- `generateContent(prompt)` — Sends a prompt directly to Gemini
- `analyzeText(text, schema)` — Analyzes text with a structured schema

Uses `VITE_GEMINI_API_KEY` environment variable.

---

## AI Models Summary

| Feature | Model | Location | When Used |
|---------|-------|----------|-----------|
| Complaint analysis | `gemini-2.0-flash` | `ComplaintAiService.php` | On complaint submission |
| Voice command interpretation | `gemini-2.5-flash` | `VoiceCommandService.php` | When local matching fails |
| Frontend AI utilities | `gemini-2.0-flash` | `useGeminiAI.js` | Browser-side AI calls |

---

## Frontend Pages Using AI

| Page | AI Feature |
|------|------------|
| `Dashboard.vue` | Displays AI-analyzed complaint data grouped by location |
| `Complaint/Create.vue` | AI spam detection banner, AI routing badge, voice fill support |
| `Complaint/Index.vue` | Voice search, filter, and create commands |
| `Complaint/Show.vue` | Displays AI summary, priority, and analysis results |
| `Welcome.vue` | "AI Powered Analytics" feature marketing |

---

## Database Tables for AI Data

| Table | Purpose |
|-------|---------|
| `complaint_ai_analyses` | Stores AI analysis results (detected category, priority, confidence score, summary) |
| `complaints.is_spam` | Boolean flag set by AI spam detection |
| `complaints.ai_summary` | AI-generated complaint summary |
| `complaints.priority` | AI-assigned priority level |
| `complaints.escalation_level` | Auto-incremented based on SLA breaches |
| `complaints.due_at` | SLA deadline calculated from AI priority |

import { GoogleGenerativeAI } from '@google/generative-ai';

const API_KEY = import.meta.env.VITE_GEMINI_API_KEY || '';

export function useGeminiAI() {
    const genAI = new GoogleGenerativeAI(API_KEY);
    const model = genAI.getGenerativeModel({ model: 'gemini-2.0-flash' });

    async function generateContent(prompt, options = {}) {
        try {
            const result = await model.generateContent(prompt, options);
            return result.response.text();
        } catch (error) {
            console.error('Gemini AI Error:', error);
            throw error;
        }
    }

    async function analyzeText(text, schema = null) {
        const prompt = schema 
            ? `${text}\n\nRespond in JSON format with this schema: ${JSON.stringify(schema)}`
            : text;
        
        return await generateContent(prompt);
    }

    return {
        generateContent,
        analyzeText,
        model,
    };
}

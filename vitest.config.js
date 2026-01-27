import { defineConfig } from "vitest/config";
import vue from "@vitejs/plugin-vue";
import path from "path";

export default defineConfig({
    plugins: [vue()],
    resolve: {
        alias: {
            "@": path.resolve(__dirname, "./resources/js"),
        },
    },
    test: {
        environment: "jsdom",
        globals: true,
        setupFiles: ["./resources/js/__tests__/setup.js"],
        coverage: {
            provider: "v8",
            reporter: ["text", "json", "html", "lcov"],
            include: ["resources/js/**/*.{js,vue}"],
            exclude: [
                "resources/js/__tests__",
                "node_modules",
            ],
            lines: 80,
            functions: 80,
            branches: 80,
            statements: 80,
        },
        include: ["resources/js/__tests__/**/*.spec.js"],
    },
});

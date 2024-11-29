import { clsx, type ClassValue } from 'clsx';
import { twMerge } from 'tailwind-merge';

/**
 * A drop-in replacement for `clsx` that supports Tailwind's merge utility.
 *
 * @param inputs - A list of class names to merge.
 * @returns A single class name string.
 *
 * @example
 * import { cn } from "@/lib/utils"
 *
 * const Example = () => <div className={cn("bg-red-500", "h-96")}>Hello!</div>
 *
 * @see https://github.com/tailwindlabs/tailwindcss/blob/3.x/src/plugins/merge.ts
 * @see https://github.com/lukeed/clsx
 */
export function cn(...inputs: ClassValue[]) {
    return twMerge(clsx(inputs));
}

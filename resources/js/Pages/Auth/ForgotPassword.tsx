import { Button } from '@/Components/ui/button';
import {
    Form,
    FormControl,
    FormDescription,
    FormField,
    FormItem,
    FormLabel,
    FormMessage,
} from '@/Components/ui/form';
import { Input } from '@/Components/ui/input';
import GuestLayout from '@/Layouts/GuestLayout';
import { zodResolver } from '@hookform/resolvers/zod';
import { Head, Link, router } from '@inertiajs/react';
import { ArrowLeft } from 'lucide-react';
import { useForm } from 'react-hook-form';
import { z } from 'zod';

const formSchema = z.object({
    email: z.string().min(1, { message: 'Email is required' }).email(),
});

export default function ForgotPassword() {
    const form = useForm<z.infer<typeof formSchema>>({
        resolver: zodResolver(formSchema),
        defaultValues: {
            email: '',
        },
    });

    function onSubmit(data: z.infer<typeof formSchema>) {
        router.post(route('password.email'), data);
    }

    return (
        <GuestLayout>
            <Head title="Forgot Password" />

            <Form {...form}>
                <form
                    className="mx-auto flex w-full max-w-80 flex-1 flex-col items-center justify-center"
                    onSubmit={form.handleSubmit(onSubmit)}
                >
                    <div className="w-full space-y-4">
                        <div>
                            <div className="mb-1 text-sm text-muted-foreground">
                                Start your journey
                            </div>
                            <h2 className="text-xl font-bold">
                                Forgot Password
                            </h2>
                        </div>

                        <FormField
                            control={form.control}
                            name="email"
                            render={({ field }) => (
                                <FormItem>
                                    <FormLabel>Email</FormLabel>
                                    <FormControl>
                                        <Input
                                            id="email"
                                            type="email"
                                            name="email"
                                            value={field.value}
                                            className="mt-1 block w-full"
                                            onChange={field.onChange}
                                        />
                                    </FormControl>
                                    <FormDescription>
                                        Forgot your password? No problem. Just
                                        let us know your email address and we
                                        will email you a password reset link
                                        that will allow you to choose a new one.
                                    </FormDescription>
                                    <FormMessage />
                                </FormItem>
                            )}
                        />
                    </div>

                    <div className="w-full pt-4">
                        <Button
                            variant="gooeyRight"
                            className="w-full"
                            type="submit"
                        >
                            Email Password Reset Link
                        </Button>
                    </div>
                </form>

                <div>
                    <Button
                        variant="linkHover2"
                        className="text-xs text-primary"
                        asChild
                    >
                        <Link href={route('login')}>
                            <ArrowLeft className="mr-1 size-3" /> Back?
                        </Link>
                    </Button>
                </div>
            </Form>
        </GuestLayout>
    );
}

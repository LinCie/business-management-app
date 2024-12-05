import GuestLayout from '@/Layouts/GuestLayout';
import { Head, Link, router } from '@inertiajs/react';

import { Button } from '@/Components/ui/button';
import { Checkbox } from '@/Components/ui/checkbox';
import {
    Form,
    FormControl,
    FormField,
    FormItem,
    FormLabel,
    FormMessage,
} from '@/Components/ui/form';
import { Input } from '@/Components/ui/input';
import { PasswordInput } from '@/Components/ui/password-input';
import { zodResolver } from '@hookform/resolvers/zod';
import { useForm } from 'react-hook-form';
import { z } from 'zod';

const formSchema = z.object({
    email: z.string().min(1, { message: 'Email is required' }).email(),
    password: z.string().min(1, { message: 'Password is required' }),
    remember: z.boolean(),
});

export default function Login({
    status,
    canResetPassword,
}: {
    status?: string;
    canResetPassword: boolean;
}) {
    const form = useForm<z.infer<typeof formSchema>>({
        resolver: zodResolver(formSchema),
        defaultValues: {
            email: '',
            password: '',
            remember: false,
        },
    });

    function onSubmit(data: z.infer<typeof formSchema>) {
        router.post(route('login'), data, {
            onFinish: () => form.reset({ password: '' }),
        });
    }

    return (
        <GuestLayout>
            <Head title="Log in" />

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
                                Login to HaeBot ERP
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
                                            placeholder="han@haebot.com"
                                            {...field}
                                        />
                                    </FormControl>
                                    <FormMessage />
                                </FormItem>
                            )}
                        />

                        <FormField
                            control={form.control}
                            name="password"
                            render={({ field }) => (
                                <FormItem>
                                    <FormLabel>Password</FormLabel>
                                    <FormControl>
                                        <PasswordInput {...field} />
                                    </FormControl>
                                    <FormMessage />
                                </FormItem>
                            )}
                        />

                        <FormField
                            control={form.control}
                            name="remember"
                            render={({ field }) => (
                                <FormItem className="flex flex-row items-start space-x-2 space-y-0">
                                    <FormControl>
                                        <Checkbox
                                            checked={field.value}
                                            onCheckedChange={field.onChange}
                                        />
                                    </FormControl>
                                    <FormLabel>Remember me</FormLabel>
                                    <FormMessage />
                                </FormItem>
                            )}
                        />

                        <div className="flex flex-col gap-2 pt-4">
                            <Button
                                className="w-full"
                                variant="gooeyRight"
                                type="submit"
                            >
                                Login
                            </Button>
                            {canResetPassword && (
                                <Link
                                    href={route('password.request')}
                                    className="rounded-md text-end text-sm text-gray-600 underline hover:text-gray-900"
                                >
                                    Forgot your password?
                                </Link>
                            )}
                        </div>

                        {status && (
                            <div className="mb-4 text-sm font-medium text-green-600">
                                {status}
                            </div>
                        )}
                    </div>
                </form>

                <div className="text-sm text-muted-foreground">
                    Need an Account?{' '}
                    <Button
                        asChild
                        variant="linkHover2"
                        className="m-0 p-0 text-primary"
                    >
                        <Link href={route('register')}>Register</Link>
                    </Button>
                </div>
            </Form>
        </GuestLayout>
    );
}

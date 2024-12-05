import GuestLayout from '@/Layouts/GuestLayout';
import { zodResolver } from '@hookform/resolvers/zod';
import { Head, Link, router } from '@inertiajs/react';
import { useForm } from 'react-hook-form';
import { z } from 'zod';

import { Button } from '@/Components/ui/button';
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

const formSchema = z
    .object({
        name: z.string().min(1, { message: 'Name is required' }),
        email: z.string().min(1, { message: 'Email is required' }).email(),
        password: z.string().min(8),
        password_confirmation: z.string().min(8),
    })
    .refine((data) => data.password === data.password_confirmation, {
        message: "Passwords don't match",
    });

export default function Register() {
    const form = useForm<z.infer<typeof formSchema>>({
        resolver: zodResolver(formSchema),
        defaultValues: {
            name: '',
            email: '',
            password: '',
            password_confirmation: '',
        },
    });

    function onSubmit(data: z.infer<typeof formSchema>) {
        router.post(route('register'), data, {
            onFinish: () =>
                form.reset({ password: '', password_confirmation: '' }),
        });
    }

    return (
        <GuestLayout>
            <Head title="Register" />

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
                                Register to HaeBot ERP
                            </h2>
                        </div>

                        <FormField
                            control={form.control}
                            name="name"
                            render={({ field }) => (
                                <FormItem>
                                    <FormLabel>Name</FormLabel>
                                    <FormControl>
                                        <Input
                                            placeholder="Ahmad Hae"
                                            {...field}
                                        />
                                    </FormControl>
                                    <FormMessage />
                                </FormItem>
                            )}
                        />
                        <FormField
                            control={form.control}
                            name="email"
                            render={({ field }) => (
                                <FormItem>
                                    <FormLabel>Email</FormLabel>
                                    <FormControl>
                                        <Input
                                            placeholder="ahmad@haebot.com"
                                            type="email"
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
                            name="password_confirmation"
                            render={({ field }) => (
                                <FormItem>
                                    <FormLabel>Confirm Password</FormLabel>
                                    <FormControl>
                                        <PasswordInput {...field} />
                                    </FormControl>
                                    <FormMessage />
                                </FormItem>
                            )}
                        />
                        <div className="pt-4">
                            <Button
                                type="submit"
                                variant="gooeyRight"
                                className="w-full"
                            >
                                Register
                            </Button>
                        </div>
                    </div>
                </form>
            </Form>

            <div className="text-sm text-muted-foreground">
                Have an Account?{' '}
                <Button
                    asChild
                    variant="linkHover2"
                    className="m-0 p-0 text-primary"
                >
                    <Link href={route('login')}>Login</Link>
                </Button>
            </div>
        </GuestLayout>
    );
}
